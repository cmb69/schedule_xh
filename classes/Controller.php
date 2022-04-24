<?php

/**
 * Copyright 2012-2022 Christoph M. Becker
 *
 * This file is part of Schedule_XH.
 *
 * Schedule_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Schedule_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Schedule_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Schedule;

class Controller
{
    /**
     * Dispatches on plugin related requests.
     *
     * @return void
     */
    public function dispatch()
    {
        if (XH_ADM) {
            XH_registerStandardPluginMenuItems(false);
            if ($this->isAdministrationRequested()) {
                $this->handleAdministration();
            }
        }
    }

    /**
     * Returns whether the administration is requested.
     *
     * @return bool
     *
     * @global string Whether the plugin administration is requested.
     */
    protected function isAdministrationRequested()
    {
        global $schedule;

        return function_exists('XH_wantsPluginAdministration')
            && XH_wantsPluginAdministration('schedule')
            || isset($schedule) && $schedule == 'true';
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
                $o .= $this->about() . '<hr>' . $this->systemCheck();
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
     * @global array The localization of the plugins.
     */
    protected function about()
    {
        global $pth, $plugin_tx;

        $icon =
            '<img src="' . $pth['folder']['plugins'] . 'schedule/schedule.png"'
            . ' alt="' . $plugin_tx['schedule']['alt_logo']
            . '" class="schedule_logo">';
        $bag = array(
            'heading' => 'Schedule &ndash; ' . $plugin_tx['schedule']['menu_info'],
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
        global $pth, $plugin_tx;

        $phpVersion = '7.1.0';
        $ptx = $plugin_tx['schedule'];
        $imgdir = $pth['folder']['plugins'] . 'schedule/images/';
        $ok = '<img src="' . $imgdir . 'ok.png" alt="ok">';
        $warn = '<img src="' . $imgdir . 'warn.png" alt="warning">';
        $fail = '<img src="' . $imgdir . 'fail.png" alt="failure">';
        $o = '<h4>' . $ptx['syscheck_title'] . '</h4>'
            . (version_compare(PHP_VERSION, $phpVersion) >= 0 ? $ok : $fail)
            . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_phpversion'], $phpVersion)
            . '<br>';
        foreach (array('session') as $ext) {
            $o .= (extension_loaded($ext) ? $ok : $fail)
                . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_extension'], $ext)
                . '<br>';
        }
        $o .= '<br>';
        foreach (array('config/', 'css/', 'languages/') as $folder) {
            $folders[] = $pth['folder']['plugins'] . 'schedule/' . $folder;
        }
        $folders[] = $this->dataFolder();
        foreach ($folders as $folder) {
            $o .= (is_writable($folder) ? $ok : $warn)
                . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_writable'], $folder)
                . '<br>';
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
        $isMulti = is_bool($options[0])
            ? array_shift($options) : $pcf['default_multi'];
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
        return $this->planner($name, $options, $recs, $showTotals, $readOnly, $isMulti);
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
            if (mkdir($fn, 0777, true)) {
                chmod($fn, 0777);
            } else {
                e('cntwriteto', 'folder', $fn);
            }
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
     * Locks resp. unlocks the voting file.
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
     *
     * @global array The configuration of the plugins.
     */
    protected function read($name, $readOnly)
    {
        global $plugin_cf;

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
        if ($plugin_cf['schedule']['sort_users']) {
            ksort($recs);
        }
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
     * @param bool   $isMulti    Whether the planner allows multiple options.
     *
     * @return string  (X)HTML.
     *
     * @global string   The name of the site.
     * @global string   The GET parameter of the current page.
     * @global string   The localization of the core.
     * @global string   The localization of the plugins.
     */
    protected function planner($name, $options, $recs, $showTotals, $readOnly, $isMulti)
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
                $checked = $ok ? ' checked="checked"' : '';
                $type = $isMulti ? 'checkbox' : 'radio';
                $cells[$user][$option] = $user == $currentUser
                    ?
                        '<input type="' . $type . '" name="schedule_date_' . $name
                        . '[]" value="' . $option . '"' . $checked . '>'
                    : '&nbsp;';
            }
        }
        if ($currentUser) {
            $iname = 'schedule_submit_' . $name;
            $submit =
                '<input type="submit" class="submit" name="' . $iname
                . '" value="' . ucfirst($tx['action']['save']) . '">';
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
