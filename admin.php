<?php

use Plib\Request;
use Schedule\Dic;

if (!defined("CMSIMPLE_XH_VERSION")) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

/**
 * @var string $admin
 * @var string $o
 */

XH_registerStandardPluginMenuItems(true);

if (XH_wantsPluginAdministration("schedule")) {
    $o .= print_plugin_admin("on");
    switch ($admin) {
        case "":
            $o .= Dic::makeInfoController()()();
            break;
        case "plugin_main":
            $o .= Dic::callBuilder()(Request::current())();
            break;
        default:
            $o .= plugin_admin_common();
    }
}
