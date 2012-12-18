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


define('SCHEDULE_VERSION', '1beta1');


function Schedule_dataFolder()
{
    global $pth;

    return $pth['folder']['plugins'] . 'schedule/data/';
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
 * @return array
 */
function Schedule_read($event)
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
    if (($user = Schedule_user()) !== null && !isset($recs[$user])) {
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
 *
 */
function Schedule_view($event, $dates, $recs) // TODO: rename $dates to $options
{
    global $sn, $su, $tx;

    $url = "$sn?$su";
    $o = '<form class="schedule" action="' . $url . '" method="POST">'
        . '<table class="schedule"><tbody>'
        . '<tr><th></th>';
    foreach ($dates as $date) {
        $o .= '<th>' . $date . '</th>';
    }
    $o .= '</tr>';
    foreach ($recs as $user => $rec) {
        $o .= '<tr>'
            . '<td class="schedule_user">' . $user . '</td>';
        foreach ($dates as $date) {
            $ok = array_search($date, $rec) !== false;
            $class = 'schedule_' . ($ok ? 'green' : 'red');
            $checked = $ok ? ' checked="checked"' : '';
            $cell = $user == Schedule_user()
                ? tag('input type="checkbox" name="schedule_date_' . $event
                      . '[]" value="' . $date . '"' . $checked)
                : '&nbsp;';
            $o .= '<td class="' . $class . '">' . $cell . '</td>';
        }
        $o .= '</tr>';
    }
    if (Schedule_user()) {
        $o .= '<tr class="schedule_buttons"><td colspan="4">'
            . tag('input type="submit" class="submit" name="schedule_submit_' . $event
                  . '" value="' . ucfirst($tx['action']['save']) . '"')
            . '</td></tr>';
    }
    $o .= '</tbody></table></form>';

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
    global $plugin_tx;

    $ptx = $plugin_tx['schedule'];

    if (!preg_match('/^[a-z\-0-9]+$/i', $event)) {
        return '<p class="cmsimplecore_warning">' . $ptx['err_invalid_name']
            . '</p>';
    }

    $options = func_get_args();
    array_shift($options);
    if (empty($options)) {
        return '<p class="cmsimplecore_warning">' . $ptx['err_no_options']
            . '</p>';
    }

    Schedule_lock($event,
                  isset($_POST['schedule_submit_' . $event])
                    ? LOCK_EX
                    : LOCK_SH);

    $recs = Schedule_read($event);

    if (isset($_POST['schedule_submit_' . $event]) && Schedule_user()) {
        $recs = Schedule_submit($event, $options, $recs);
    }

    Schedule_lock($event, LOCK_UN);

    return Schedule_view($event, $options, $recs);
}

?>
