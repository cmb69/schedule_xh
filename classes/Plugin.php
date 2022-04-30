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
                $o .= self::about() . '<hr>' . self::systemCheck();
                break;
            default:
                $o .= plugin_admin_common();
        }
    }

    private static function about(): string
    {
        global $pth, $plugin_tx;

        $icon =
            '<img src="' . $pth['folder']['plugins'] . 'schedule/schedule.png"'
            . ' alt="' . $plugin_tx['schedule']['alt_logo']
            . '" class="schedule_logo">';
        $bag = array(
            'heading' => 'Schedule &ndash; ' . $plugin_tx['schedule']['menu_info'],
            'icon' => $icon,
            'version' => self::VERSION
        );
        return self::view('about', $bag);
    }

    private static function systemCheck(): string
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
        $folders[] = self::dataFolder();
        foreach ($folders as $folder) {
            $o .= (is_writable($folder) ? $ok : $warn)
                . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_writable'], $folder)
                . '<br>';
        }
        return $o;
    }

    /**
     * @param mixed $args
     */
    public static function main(string $name, ...$args): string
    {
        $controller = new MainController();
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

    /**
     * @param array<string,mixed> $bag
     */
    public static function view(string $template, array $bag): string
    {
        global $pth;

        ob_start();
        extract($bag);
        include $pth['folder']['plugins'] . 'schedule/views/' . $template . '.htm';
        return ob_get_clean();
    }
}
