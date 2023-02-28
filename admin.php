<?php

use Schedule\Dic;
use Schedule\Infra\Request;

if (!defined("CMSIMPLE_XH_VERSION")) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

/**
 * @var string $admin
 * @var string $o
 */

XH_registerStandardPluginMenuItems(false);

if (XH_wantsPluginAdministration('schedule')) {
    $o .= print_plugin_admin('off');
    switch ($admin) {
        case '':
            $o .= Dic::makeInfoController()->execute(new Request)->fire();
            break;
        default:
            $o .= plugin_admin_common();
    }
}
