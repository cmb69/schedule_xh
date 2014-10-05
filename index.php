<?php

/**
 * Front-end of Schedule_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Schedule
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2012-2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Schedule_XH
 */

/*
 * Prevent direct access and usage from unsupported CMSimple_XH versions.
 */
if (!defined('CMSIMPLE_XH_VERSION')
    || strpos(CMSIMPLE_XH_VERSION, 'CMSimple_XH') !== 0
    || version_compare(CMSIMPLE_XH_VERSION, 'CMSimple_XH 1.6', 'lt')
) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: text/plain; charset=UTF-8');
    die(<<<EOT
Schedule_XH detected an unsupported CMSimple_XH version.
Uninstall Schedule_XH or upgrade to a supported CMSimple_XH version!
EOT
    );
}

/**
 * The model.
 */
require_once $pth['folder']['plugin_classes'] . 'Model.php';

/**
 * The schedule class.
 */
require_once $pth['folder']['plugin_classes'] . 'Schedule.php';

/**
 * The plugin version.
 */
define('SCHEDULE_VERSION', '@SCHEDULE_VERSION@');

/**
 * The schedule object.
 *
 * @var Schedule
 */
$_schedule = new Schedule();
$_schedule->dispatch();

/**
 * The main function.
 *
 * @param string $name A voting name.
 *
 * @return string (X)HTML.
 */
function schedule($name)
{
    global $_schedule;

    return call_user_func_array(array($_schedule, 'main'), func_get_args());
}

$_Schedule_model = new Schedule_Model();

?>
