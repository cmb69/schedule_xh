<?php

use Schedule\Dic;

/**
 * @var string $admin
 * @var string $o
 */

XH_registerStandardPluginMenuItems(false);

if (XH_wantsPluginAdministration('schedule')) {
    $o .= print_plugin_admin('off');
    switch ($admin) {
        case '':
            $o .= Dic::makeInfoController()->execute();
            break;
        default:
            $o .= plugin_admin_common();
    }
}
