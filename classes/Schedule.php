<?php

/**
 * The schedule class.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Schedule
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2012-2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Schedule_XH
 */

/**
 * The schedule class.
 *
 * @category CMSimple_XH
 * @package  Schedule
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Schedule_XH
 */
class Schedule
{
    /**
     * Dispatches on plugin related requests.
     *
     * @return void
     */
    public function dispatch()
    {
        global $schedule;

        if (isset($schedule) && $schedule == 'true') {
            $this->handleAdministration();
        }
    }

    /**
     * Handles the plugin administration.
     *
     * @return void
     *
     * @global string The value of the admin GP parameter.
     * @global string The value of the action GP parameter.
     * @global string The (X)HTML of the contents area.
     */
    protected function handleAdministration()
    {
        global $admin, $action, $o;

        $o .= print_plugin_admin('off');
        switch ($admin) {
        case '':
            $o .= $this->about() . tag('hr') . $this->systemCheck();
            break;
        default:
            $o .= plugin_admin_common($action, $admin, 'schedule');
        }
    }

    /**
     * Returns the plugin's About view.
     *
     * @return string (X)HTML.
     *
     * @global array The paths of system files and folders.
     */
    protected function about()
    {
        global $pth;

        $icon = tag(
            'img src="' . $pth['folder']['plugins']
            . 'schedule/schedule.png" alt="Plugin Icon"'
        );
        $bag = array(
            'heading' => 'Schedule_XH',
            'url' => 'http://3-magi.net/?CMSimple_XH/Schedule_XH',
            'icon' => $icon,
            'version' => SCHEDULE_VERSION
        );
        return $this->view('about', $bag);
    }

    /**
     * Returns the requirements information view.
     *
     * @return string (X)HTML.
     *
     * @global array    The paths of system files and folders.
     * @global array    The localization of the core.
     * @global array    The localization of the plugins.
     */
    protected function systemCheck()
    {
        global $pth, $tx, $plugin_tx;

        $phpVersion = '4.3.0';
        $ptx = $plugin_tx['schedule'];
        $imgdir = $pth['folder']['plugins'] . 'schedule/images/';
        $ok = tag('img src="' . $imgdir . 'ok.png" alt="ok"');
        $warn = tag('img src="' . $imgdir . 'warn.png" alt="warning"');
        $fail = tag('img src="' . $imgdir . 'fail.png" alt="failure"');
        $o = '<h4>' . $ptx['syscheck_title'] . '</h4>'
            . (version_compare(PHP_VERSION, $phpVersion) >= 0 ? $ok : $fail)
            . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_phpversion'], $phpVersion)
            . tag('br');
        foreach (array('pcre', 'session') as $ext) {
            $o .= (extension_loaded($ext) ? $ok : $fail)
                . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_extension'], $ext)
                . tag('br');
        }
        $o .= (!get_magic_quotes_runtime() ? $ok : $fail)
            . '&nbsp;&nbsp;' . $ptx['syscheck_magic_quotes'] . tag('br') . tag('br');
        $o .= (strtoupper($tx['meta']['codepage']) == 'UTF-8' ? $ok : $warn)
            . '&nbsp;&nbsp;' . $ptx['syscheck_encoding'] . tag('br'). tag('br');
        foreach (array('config/', 'css/', 'languages/') as $folder) {
            $folders[] = $pth['folder']['plugins'] . 'schedule/' . $folder;
        }
        $folders[] = $this->dataFolder();
        foreach ($folders as $folder) {
            $o .= (is_writable($folder) ? $ok : $warn)
                . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_writable'], $folder)
                . tag('br');
        }
        return $o;
    }

    /**
     * The main method.
     *
     * @param string $name A voting name.
     *
     * @return string (X)HTML.
     *
     * @global array The plugin's configuration.
     * @global array The plugin's localization.
     */
    public function main($name)
    {
        global $plugin_cf, $plugin_tx;

        $pcf = $plugin_cf['schedule'];
        $ptx = $plugin_tx['schedule'];

        if (!preg_match('/^[a-z\-0-9]+$/i', $name)) {
            return '<p class="cmsimplecore_warning">' . $ptx['err_invalid_name']
                . '</p>';
        }

        $options = func_get_args();
        array_shift($options);
        $showTotals = is_bool($options[0])
            ? array_shift($options) : $pcf['default_totals'];
        $readOnly = is_bool($options[0])
            ? array_shift($options) : $pcf['default_readonly'];
        if (empty($options)) {
            return '<p class="cmsimplecore_warning">' . $ptx['err_no_option']
                . '</p>';
        }

        $posting = isset($_POST['schedule_submit_' . $name]);
        $this->lock($name, $posting ? LOCK_EX : LOCK_SH);
        $recs = $this->read($name, $readOnly);
        if ($posting && $this->user() && !$readOnly) {
            $recs = $this->submit($name, $options, $recs);
        }
        $this->lock($name, LOCK_UN);
        return $this->planner($name, $options, $recs, $showTotals, $readOnly);
    }

    /**
     * Returns the data folder.
     *
     * @return string
     *
     * @global array The paths of system files and folders.
     * @global array The plugin configuration.
     */
    protected function dataFolder()
    {
        global $pth, $plugin_cf;

        $pcf = $plugin_cf['schedule'];
        if (!empty($pcf['folder_data'])) {
            $fn = $pth['folder']['base'] . $pcf['folder_data'];
        } else {
            $fn = $pth['folder']['plugins'] . 'schedule/data/';
        }
        if (substr($fn, -1) != '/') {
            $fn .= '/';
        }
        if (file_exists($fn)) {
            if (!is_dir($fn)) {
                e('cntopen', 'folder', $fn);
            }
        } else {
            if (!mkdir($fn, 0777, true)) {
                e('cntwriteto', 'folder', $fn);
            }
            // TODO: chmod()
        }
        return $fn;
    }

    /**
     * Returns the currently logged in user.
     *
     * @return string
     */
    protected function user()
    {
        if (session_id() == '') {
            session_start();
        }
        return isset($_SESSION['username'])
            ? $_SESSION['username']
            : (isset($_SESSION['Name'])
                ? $_SESSION['Name']
                : null);
    }

    /**
     * (Un)locks the voting file.
     *
     * @param string $name The voting name.
     * @param int    $mode The lock operation.
     *
     * @return void
     *
     * @staticvar array The file handles.
     */
    protected function lock($name, $mode)
    {
        static $fhs = array();

        $fn = $this->dataFolder() . $name . '.lck';
        if ($mode == LOCK_SH || $mode == LOCK_EX) {
            if (isset($fhs[$name])) {
                $msg = __FUNCTION__ . '(): $fn is already locked by this request!';
                trigger_error($msg, E_USER_WARNING);
                return;
            }
            $fhs[$name] = fopen($fn, 'c');
            flock($fhs[$name], $mode);
        } else {
            flock($fhs[$name], LOCK_UN);
            unset($fhs[$name]);
        }
    }

    /**
     * Returns all stored records of the voting.
     *
     * @param string $name     The name of the voting.
     * @param bool   $readOnly Whether the planer is read only.
     *
     * @return array
     */
    protected function read($name, $readOnly)
    {
        $recs = array();
        $fn = $this->dataFolder() . $name . '.csv';
        if (!file_exists($fn)) {
            touch($fn);
        }
        if (($lines = file($fn)) === false) {
            e('cntopen', 'file', $fn);
            return false;
        }
        foreach ($lines as $line) {
            $rec = explode("\t", rtrim($line));
            $user = array_shift($rec);
            $recs[$user] = $rec;
        }
        if (!$readOnly
            && ($user = $this->user()) !== null && !isset($recs[$user])
        ) {
            $recs[$user] = array();
        }
        ksort($recs);
        return $recs;
    }

    /**
     * Saves the records of the voting.
     *
     * @param string $name The name of the voting.
     * @param array  $recs The voting records.
     *
     * @return void
     */
    protected function write($name, $recs)
    {
        $lines = array();
        foreach ($recs as $user => $rec) {
            array_unshift($rec, $user);
            $line = implode("\t", $rec);
            $lines[] = $line;
        }
        $o = implode("\n", $lines) . "\n";
        $fn = $this->dataFolder() . $name . '.csv';
        if (($fh = fopen($fn, 'wb')) === false || fwrite($fh, $o) === false) {
            e('cntsave', 'file', $fn);
        }
        if ($fh !== false) {
            fclose($fh);
        }
    }

    /**
     * Returns the view of a template.
     *
     * @param string $template Path to the template.
     * @param array  $bag      The data for the view.
     *
     * @return string (X)HTML.
     *
     * @global array The paths of system files and folders.
     */
    protected function view($template, $bag)
    {
        global $pth;

        ob_start();
        extract($bag);
        include $pth['folder']['plugins'] . 'schedule/views/' . $template . '.htm';
        return ob_get_clean();
    }

    /**
     * Returns the planner view.
     *
     * @param string $name       The name of the voting.
     * @param array  $options    The options.
     * @param array  $recs       The stored votings.
     * @param bool   $showTotals Whether to show the totals.
     * @param bool   $readOnly   Whether the planner is read only.
     *
     * @return string  (X)HTML.
     *
     * @global string   The name of the site.
     * @global string   The GET parameter of the current page.
     * @global string   The localization of the core.
     * @global string   The localization of the plugins.
     */
    protected function planner($name, $options, $recs, $showTotals, $readOnly)
    {
        global $sn, $su, $tx, $plugin_tx;

        $currentUser = $readOnly ? null : $this->user();
        $counts = array();
        foreach ($options as $option) {
            $counts[$option] = 0;
        }
        $users = array();
        $cells = array();
        foreach ($recs as $user => $rec) {
            $users[$user] = array();
            $cells[$user] = array();
            foreach ($options as $option) {
                $ok = array_search($option, $rec) !== false;
                $users[$user][$option] = $ok;
                if ($ok) {
                    $counts[$option]++;
                }
                $class = 'schedule_' . ($ok ? 'green' : 'red');
                $checked = $ok ? ' checked="checked"' : '';
                $cells[$user][$option] = $user == $currentUser
                    ? tag(
                        'input type="checkbox" name="schedule_date_' . $name
                        . '[]" value="' . $option . '"' . $checked
                    )
                    : '&nbsp;';
            }
        }
        if ($currentUser) {
            $iname = 'schedule_submit_' . $name;
            $submit = tag(
                'input type="submit" class="submit" name="' . $iname
                . '" value="' . ucfirst($tx['action']['save']) . '"'
            );
        } else {
            $submit = '';
        }
        $bag = array('showTotals'=> $showTotals,
                     'ptx' => $plugin_tx['schedule'],
                     'currentUser' => $readOnly ? null : $this->user(),
                     'url' => "$sn?$su",
                     'options' => $options,
                     'counts' => $counts,
                     'users' => $users,
                     'cells' => $cells,
                     'submit' => $submit);
        return $this->view('planner', $bag);
    }

    /**
     * Handles the form submission and returns the current records.
     *
     * @param string $name    The name of the voting.
     * @param array  $options The options.
     * @param array  $recs    The stored votings.
     *
     * @return array
     */
    protected function submit($name, $options, $recs)
    {
        $fields = isset($_POST['schedule_date_' . $name])
            ? $_POST['schedule_date_' . $name]
            : array();
        $rec = array();
        foreach ($fields as $field) {
            $field = stsl($field);
            if (array_search($field, $options) === false) {
                // user voted for invalid option, what's normally not possible
                return $recs;
            }
            $rec[] = $field;
        }
        $recs[$this->user()] = $rec;
        ksort($recs);
        $this->write($name, $recs);
        return $recs;
    }
}

?>
