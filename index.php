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
 * The plugin version.
 */
define('SCHEDULE_VERSION', '@SCHEDULE_VERSION@');

/**
 * Autoloads the plugin classes.
 *
 * @param string $class A class name.
 *
 * @return void
 */
function Schedule_autoload($class)
{
    global $pth;
    $parts = explode('_', $class, 2);
    if ($parts[0] == 'Schedule') {
        include_once $pth['folder']['plugins'] . 'schedule/classes/'
            . $parts[1] . '.php';
    }
}

spl_autoload_register('Schedule_autoload');

/**
 * The schedule object.
 *
 * @var Schedule
 */
$_Schedule_controller = new Schedule_Controller();
$_Schedule_controller->dispatch();

/**
 * The main function.
 *
 * @param string $name A voting name.
 *
 * @return string (X)HTML.
 */
function schedule($name)
{
    global $_Schedule_controller;

    return call_user_func_array(
        array($_Schedule_controller, 'main'), func_get_args()
    );
}

?>
