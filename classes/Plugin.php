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

    public static function main(string $name): string
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
        $isMulti = is_bool($options[0])
            ? array_shift($options) : $pcf['default_multi'];
        if (empty($options)) {
            return '<p class="cmsimplecore_warning">' . $ptx['err_no_option']
                . '</p>';
        }

        $votingService = new VotingService(self::dataFolder());
        $posting = isset($_POST['schedule_submit_' . $name]);
        if (!$posting || self::user() === null || $readOnly) {
            $user = (!$readOnly && self::user() !== null) ? self::user() : null;
            $recs = $votingService->findAll($name, $user, $pcf['sort_users']);
        } else {
            $submission = self::submit($name, $options);
            $user = self::user();
            if ($submission !== null) {
                $votingService->vote($name, $user, $submission);
            }
            $recs = $votingService->findAll($name, $user, $pcf['sort_users']);
        }
        return self::planner($name, $options, $recs, $showTotals, $readOnly, $isMulti);
    }

    private static function dataFolder(): string
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

    private static function user(): ?string
    {
        if (session_id() == '') {
            session_start();
        }
        return isset($_SESSION['username'])
            ? $_SESSION['username']
            : (isset($_SESSION['Name'])
                ? $_SESSION['Name']
                : null);
    }

    /**
     * @param array<string,mixed> $bag
     */
    private static function view(string $template, array $bag): string
    {
        global $pth;

        ob_start();
        extract($bag);
        include $pth['folder']['plugins'] . 'schedule/views/' . $template . '.htm';
        return ob_get_clean();
    }

    /**
     * @param array<string> $options
     * @param array<string,array<string>> $recs
     */
    private static function planner(
        string $name,
        array $options,
        array $recs,
        bool $showTotals,
        bool $readOnly,
        bool $isMulti
    ): string {
        global $sn, $su, $tx, $plugin_tx;

        $currentUser = $readOnly ? null : self::user();
        $counts = array();
        foreach ($options as $option) {
            $counts[$option] = 0;
        }
        $users = array();
        $cells = array();
        foreach ($recs as $user => $rec) {
            $users[$user] = array();
            $cells[$user] = array();
            foreach ($options as $option) {
                $ok = array_search($option, $rec) !== false;
                $users[$user][$option] = $ok;
                if ($ok) {
                    $counts[$option]++;
                }
                $checked = $ok ? ' checked="checked"' : '';
                $type = $isMulti ? 'checkbox' : 'radio';
                $cells[$user][$option] = $user == $currentUser
                    ?
                        '<input type="' . $type . '" name="schedule_date_' . $name
                        . '[]" value="' . $option . '"' . $checked . '>'
                    : '&nbsp;';
            }
        }
        if ($currentUser) {
            $iname = 'schedule_submit_' . $name;
            $submit =
                '<input type="submit" class="submit" name="' . $iname
                . '" value="' . ucfirst($tx['action']['save']) . '">';
        } else {
            $submit = '';
        }
        $bag = array('showTotals'=> $showTotals,
                     'ptx' => $plugin_tx['schedule'],
                     'currentUser' => $readOnly ? null : self::user(),
                     'url' => "$sn?$su",
                     'options' => $options,
                     'counts' => $counts,
                     'users' => $users,
                     'cells' => $cells,
                     'submit' => $submit);
        return self::view('planner', $bag);
    }

    /**
     * @param array<string> $options
     * @return array<string>
     */
    private static function submit(string $name, array $options): ?array
    {
        $fields = isset($_POST['schedule_date_' . $name])
            ? $_POST['schedule_date_' . $name]
            : array();
        $rec = array();
        foreach ($fields as $field) {
            if (array_search($field, $options) === false) {
                // user voted for invalid option, what's normally not possible
                return null;
            }
            $rec[] = $field;
        }
        return $rec;
    }
}
