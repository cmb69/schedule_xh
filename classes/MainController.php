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

final class MainController
{
    /**
     * @param mixed $args
     */
    public function execute(string $name, ...$args): string
    {
        global $plugin_cf, $plugin_tx;

        $pcf = $plugin_cf['schedule'];
        $ptx = $plugin_tx['schedule'];

        if (!preg_match('/^[a-z\-0-9]+$/i', $name)) {
            return '<p class="cmsimplecore_warning">' . $ptx['err_invalid_name']
                . '</p>';
        }

        $options = $args;
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

        $votingService = new VotingService(Plugin::dataFolder());
        $posting = isset($_POST['schedule_submit_' . $name]);
        if (!$posting || $this->user() === null || $readOnly) {
            $user = (!$readOnly && $this->user() !== null) ? $this->user() : null;
            $recs = $votingService->findAll($name, $user, $pcf['sort_users']);
        } else {
            $submission = $this->submit($name, $options);
            $user = $this->user();
            if ($submission !== null) {
                $votingService->vote($name, $user, $submission);
            }
            $recs = $votingService->findAll($name, $user, $pcf['sort_users']);
        }
        return $this->planner($name, $options, $recs, $showTotals, $readOnly, $isMulti);
    }

    /**
     * @param array<string> $options
     * @param array<string,array<string>> $recs
     */
    private function planner(
        string $name,
        array $options,
        array $recs,
        bool $showTotals,
        bool $readOnly,
        bool $isMulti
    ): string {
        global $sn, $su, $plugin_tx;

        $currentUser = $readOnly ? null : $this->user();
        $counts = [];
        foreach ($options as $option) {
            $counts[$option] = 0;
        }
        $users = [];
        $cells = [];
        foreach ($recs as $user => $rec) {
            $users[$user] = [];
            $cells[$user] = [];
            foreach ($options as $option) {
                $ok = array_search($option, $rec) !== false;
                $users[$user][$option] = $ok ? "schedule_green" : "schedule_red";
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
                . '" value="' . $plugin_tx['schedule']['label_save'] . '">';
        } else {
            $submit = '';
        }
        $bag = [
            'showTotals'=> $showTotals,
            'currentUser' => $readOnly ? null : $this->user(),
            'url' => "$sn?$su",
            'options' => $options,
            'counts' => $counts,
            'users' => $users,
            'cells' => $cells,
            'submit' => $submit,
            'columns' => count($options) + 1,
        ];
        return Plugin::view('planner', $bag);
    }

    /**
     * @param array<string> $options
     * @return array<string>
     */
    private function submit(string $name, array $options): ?array
    {
        $fields = $_POST['schedule_date_' . $name] ?? [];
        $rec = [];
        foreach ($fields as $field) {
            if (array_search($field, $options) === false) {
                // user voted for invalid option, what's normally not possible
                return null;
            }
            $rec[] = $field;
        }
        return $rec;
    }

    private function user(): ?string
    {
        return $_SESSION['username'] ?? ($_SESSION['Name'] ?? null);
    }
}
