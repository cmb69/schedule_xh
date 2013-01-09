<?php

/**
 * Front-end of Schedule_XH.
 *
 * Copyright (c) 2012 Christoph M. Becker (see license.txt)
 */


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


define('SCHEDULE_VERSION', '1beta3');


/**
 * Returns the data folder.
 *
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
 * (Un)locks the event file.
 *
 * @param  string $event  The event name.
 * @param  int $mode  The lock operation.
 * @return void
 */
function Schedule_lock($event, $mode)
{
    static $fhs = array();

    $fn = Schedule_dataFolder() . $event . '.lck';
    if ($mode == LOCK_SH || $mode == LOCK_EX) {
        // TODO: what if isset($fhs[$event]) ???
        $fhs[$event] = fopen($fn, 'c');
        flock($fhs[$event], $mode);
    } else {
        flock($fhs[$event], LOCK_UN);
        unset($fhs[$event]);
    }
}


/**
 * Returns all stored records for the event.
 *
 * @param  string $event  The name of the event.
 * @param  bool $readOnly  Whether the planer is read only.
 * @return array
 */
function Schedule_read($event, $readOnly)
{
    $recs = array();
    $fn = Schedule_dataFolder() . $event . '.csv';
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
 * Saves the given records for the event.
 *
 * @param  string $event  The name of the event.
 * @param  array $recs
 * @return void
 */
function Schedule_write($event, $recs)
{
    $lines = array();
    foreach ($recs as $user => $rec) {
        array_unshift($rec, $user);
        $line = implode("\t", $rec);
        $lines[] = $line;
    }
    $o = implode("\n", $lines) . "\n";
    $fn = Schedule_dataFolder() . $event . '.csv';
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
 * @param  string $event  The name of the event.
 * @param  array $dates  The dates.
 * @param  array $recs
 * @param  bool $showTotals
 * @param  bool $readOnly
 * @return string  The (X)HTML.
 */
function Schedule_view($event, $dates, $recs, $showTotals, $readOnly) // TODO: rename $dates to $options
{
    global $sn, $su, $tx, $plugin_tx;

    $ptx = $plugin_tx['schedule'];
    $url = "$sn?$su";
    $o = $readOnly
	? '<div class="schedule">'
	: '<form class="schedule" action="' . $url . '" method="POST">';
    $o .= '<table class="schedule"><thead>'
        . '<tr><th></th>';
    foreach ($dates as $date) {
        $o .= '<th>' . $date . '</th>';
    }
    $o .= '</tr></thead>';
    $counts = array();
    foreach ($dates as $date) {
        $counts[$date] = 0;
    }
    $o .= '<tbody>';
    foreach ($recs as $user => $rec) {
        $o .= '<tr>'
            . '<td class="schedule_user">' . $user . '</td>';
        foreach ($dates as $date) {
            $ok = array_search($date, $rec) !== false;
            if ($ok) {
                $counts[$date]++;
            }
            $class = 'schedule_' . ($ok ? 'green' : 'red');
            $checked = $ok ? ' checked="checked"' : '';
            $cell = !$readOnly && $user == Schedule_user()
                ? tag('input type="checkbox" name="schedule_date_' . $event
                      . '[]" value="' . $date . '"' . $checked)
                : '&nbsp;';
            $o .= '<td class="' . $class . '">' . $cell . '</td>';
        }
        $o .= '</tr>';
    }
    if ($showTotals) {
        $o .= '<tr class="schedule_total"><td class="schedule_user">' . $ptx['total'] . '</td>';
        foreach ($counts as $count) {
            $o .= '<td>' . $count . '</td>';
        }
        $o .= '</tr>';
    }
    if (!$readOnly && Schedule_user()) {
        $o .= '<tr class="schedule_buttons"><td colspan="4">'
            . tag('input type="submit" class="submit" name="schedule_submit_' . $event
                  . '" value="' . ucfirst($tx['action']['save']) . '"')
            . '</td></tr>';
    }
    $o .= '</tbody></table>';
    $o .= $readOnly ? '</div>' : '</form>';

    return $o;
}


/**
 * Handles the form submission and returns the current records.
 *
 * @param  string $event  The name of the event.
 * @param  array $options  The options.
 * @param  array $recs
 * @return array
 */
function Schedule_submit($event, $options, $recs)
{
    $fields = isset($_POST['schedule_date_' . $event])
        ? $_POST['schedule_date_' . $event]
        : array();
    $rec = array();
    foreach ($fields as $field) {
        $field = stsl($field);
        if (array_search($field, $options) === false) {
            return $recs;
        }
        $rec[] = $field;
    }
    $recs[Schedule_user()] = $rec;
    ksort($recs);
    Schedule_write($event, $recs);
    return $recs;
}


/**
 * @access public
 * @param  string $event  The event name.
 * @return string  The (X)HTML.
 */
function Schedule($event)
{
    global $plugin_cf, $plugin_tx;

    $pcf = $plugin_cf['schedule'];
    $ptx = $plugin_tx['schedule'];

    if (!preg_match('/^[a-z\-0-9]+$/i', $event)) {
        return '<p class="cmsimplecore_warning">' . $ptx['err_invalid_name']
            . '</p>';
    }

    $options = func_get_args();
    array_shift($options);
    $showTotals = is_bool($options[0]) ? array_shift($options) : $pcf['default_totals'];
    $readOnly = is_bool($options[0]) ? array_shift($options) : $pcf['default_readonly'];
    if (empty($options)) {
        return '<p class="cmsimplecore_warning">' . $ptx['err_no_option']
            . '</p>';
    }

    Schedule_lock($event,
                  isset($_POST['schedule_submit_' . $event])
                    ? LOCK_EX
                    : LOCK_SH);

    $recs = Schedule_read($event, $readOnly);

    if (isset($_POST['schedule_submit_' . $event]) && Schedule_user()) {
        $recs = Schedule_submit($event, $options, $recs);
    }

    Schedule_lock($event, LOCK_UN);

    return Schedule_view($event, $options, $recs, $showTotals, $readOnly);
}

?>
