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

final class Plugin
{
    private const VERSION = '2.0-dev';

    public static function dispatch(): void
    {
        if (XH_ADM) { // @phpstan-ignore-line
            XH_registerStandardPluginMenuItems(false);
            if (self::isAdministrationRequested()) {
                self::handleAdministration();
            }
        }
    }

    private static function isAdministrationRequested(): bool
    {
        return XH_wantsPluginAdministration('schedule');
    }

    private static function handleAdministration(): void
    {
        global $admin, $o;

        $o .= print_plugin_admin('off');
        switch ($admin) {
            case '':
                $o .= self::about();
                break;
            default:
                $o .= plugin_admin_common();
        }
    }

    private static function about(): string
    {
        global $pth;

        $controller = new InfoController(
            self::VERSION,
            "{$pth['folder']['plugins']}schedule",
            self::dataFolder(),
            self::view()
        );
        ob_start();
        $controller->execute();
        return (string) ob_get_clean();
    }

    /**
     * @param mixed $args
     */
    public static function main(string $name, ...$args): string
    {
        global $sn, $su, $plugin_cf;

        $controller = new MainController(
            $plugin_cf["schedule"],
            "$sn?$su",
            new VotingService(self::dataFolder()),
            self::view()
        );
        return $controller->execute($name, ...$args);
    }

    public static function dataFolder(): string
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

    public static function view(): View
    {
        global $pth, $plugin_tx;

        return new View("{$pth['folder']['plugins']}schedule/views/", $plugin_tx["schedule"]);
    }
}
