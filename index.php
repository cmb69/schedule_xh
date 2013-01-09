<?php

/**
 * Front-end of Schedule_XH.
 *
 * @package	Schedule
 * @copyright	Copyright (c) 2012-2013 Christoph M. Becker <http://3-magi.net/>
 * @license	http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version     $Id$
 * @link	http://3-magi.net/?CMSimple_XH/Schedule_XH
 */


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


define('SCHEDULE_VERSION', '1beta3');

// TODO: document globals


/**
 * Returns the data folder.
 *
 * @global array  The paths of system files and folders.
 * @global array  The plugin configuration.
 * @return string
 */
function Schedule_dataFolder()
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
    }
    return $fn;
}


/**
 * Returns the currently logged in user.
 *
 * @return string
 */
function Schedule_user()
{
    if (session_id() == '') {
        session_start();
    }
    return isset($_SESSION['username']) // TODO: extend for old Memberpages
        ? $_SESSION['username']
        : null;
}


/**
 * (Un)locks the voting file.
 *
 * @param  string $name  The voting name.
 * @param  int $mode  The lock operation.
 * @return void
 */
function Schedule_lock($name, $mode)
{
    static $fhs = array();

    $fn = Schedule_dataFolder() . $name . '.lck';
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
 * @param  string $name  The name of the voting.
 * @param  bool $readOnly  Whether the planer is read only.
 * @return array
 */
function Schedule_read($name, $readOnly)
{
    $recs = array();
    $fn = Schedule_dataFolder() . $name . '.csv';
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
	 && ($user = Schedule_user()) !== null && !isset($recs[$user])) {
        $recs[$user] = array();
    }
    ksort($recs);
    return $recs;
}


/**
 * Saves the records of the voting.
 *
 * @param  string $name  The name of the voting.
 * @param  array $recs  The voting records.
 * @return void
 */
function Schedule_write($name, $recs)
{
    $lines = array();
    foreach ($recs as $user => $rec) {
        array_unshift($rec, $user);
        $line = implode("\t", $rec);
        $lines[] = $line;
    }
    $o = implode("\n", $lines) . "\n";
    $fn = Schedule_dataFolder() . $name . '.csv';
    if (($fh = fopen($fn, 'wb')) === false || fwrite($fh, $o) === false) {
        e('cntsave', 'file', $fn);
    }
    if ($fh !== false) {
        fclose($fh);
    }
}


/**
 * Returns the view.
 *
 * @todo   refactor as view
 *
 * @global string  The name of the site.
 * @global string  The GET parameter of the current page.
 * @global string  The localization of the core.
 * @global string  The localization of the plugins.
 * @param  string $name  The name of the voting.
 * @param  array $options  The options.
 * @param  array $recs  The stored votings.
 * @param  bool $showTotals  Whether to show the totals.
 * @param  bool $readOnly  Whether the planner is read only.
 * @return string  The (X)HTML.
 */
function Schedule_view($name, $options, $recs, $showTotals, $readOnly)
{
    global $sn, $su, $tx, $plugin_tx;

    $ptx = $plugin_tx['schedule'];
    $url = "$sn?$su";
    $currentUser = $readOnly
	? null
	: Schedule_user();
    $o = $currentUser
	? '<form class="schedule" action="' . $url . '" method="POST">'
	: '<div class="schedule">';
    $o .= '<table class="schedule"><thead>'
        . '<tr><th></th>';
    foreach ($options as $option) {
        $o .= '<th>' . $option . '</th>';
    }
    $o .= '</tr></thead>';
    $counts = array();
    foreach ($options as $option) {
        $counts[$option] = 0;
    }
    $o .= '<tbody>';
    foreach ($recs as $user => $rec) {
        $o .= '<tr>'
            . '<td class="schedule_user">' . $user . '</td>';
        foreach ($options as $option) {
            $ok = array_search($option, $rec) !== false;
            if ($ok) {
                $counts[$option]++;
            }
            $class = 'schedule_' . ($ok ? 'green' : 'red');
            $checked = $ok ? ' checked="checked"' : '';
            $cell = $user == $currentUser
                ? tag('input type="checkbox" name="schedule_date_' . $name
                      . '[]" value="' . $option . '"' . $checked)
                : '&nbsp;';
            $o .= '<td class="' . $class . '">' . $cell . '</td>';
        }
        $o .= '</tr>';
    }
    if ($showTotals) {
        $o .= '<tr class="schedule_total"><td class="schedule_user">'
	    . $ptx['total'] . '</td>';
        foreach ($counts as $count) {
            $o .= '<td>' . $count . '</td>';
        }
        $o .= '</tr>';
    }
    if ($currentUser) {
	$iname = 'schedule_submit_' . $name;
        $o .= '<tr class="schedule_buttons"><td colspan="4">'
            . tag('input type="submit" class="submit" name="' . $iname
                  . '" value="' . ucfirst($tx['action']['save']) . '"')
            . '</td></tr>';
    }
    $o .= '</tbody></table>';
    $o .= $currentUser ? '</form>' : '</div>';

    return $o;
}


/**
 * Handles the form submission and returns the current records.
 *
 * @param  string $name  The name of the voting.
 * @param  array $options  The options.
 * @param  array $recs  The stored votings.
 * @return array
 */
function Schedule_submit($name, $options, $recs)
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
    $recs[Schedule_user()] = $rec;
    ksort($recs);
    Schedule_write($name, $recs);
    return $recs;
}


/**
 * The controller. ;)
 *
 * @access public
 *
 * @global array  The plugin's configuration.
 * @global array  The plugin's localization.
 * @param  string $name  The voting name.
 * @return string  The (X)HTML view.
 */
function Schedule($name)
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
    Schedule_lock($name, $posting ? LOCK_EX : LOCK_SH);
    $recs = Schedule_read($name, $readOnly);
    if ($posting && Schedule_user() && !$readOnly) {
        $recs = Schedule_submit($name, $options, $recs);
    }
    Schedule_lock($name, LOCK_UN);
    return Schedule_view($name, $options, $recs, $showTotals, $readOnly);
}

?>
