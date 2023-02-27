<?php

/**
 * Copyright (c) Christoph M. Becker
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

use Schedule\Infra\View;
use Schedule\Infra\VotingService;

final class MainController
{
    /** @var array<string,string> */
    private $conf;

    /** @var string */
    private $url;

    /** @var VotingService */
    private $votingService;

    /** @var View */
    private $view;

    /** @var string|null */
    private $currentUser;

    /** @param array<string,string> $conf */
    public function __construct(
        array $conf,
        string $url,
        VotingService $votingService,
        View $view,
        ?string $currentUser
    ) {
        $this->conf = $conf;
        $this->url = $url;
        $this->votingService = $votingService;
        $this->view = $view;
        $this->currentUser = $currentUser;
    }

    /**
     * @param mixed $args
     */
    public function execute(string $name, ...$args): string
    {
        if (!preg_match('/^[a-z\-0-9]+$/i', $name)) {
            return $this->view->fail("err_invalid_name");
        }
        [$showTotals, $readOnly, $isMulti, $options] = $this->parseArguments($args);
        if (empty($options)) {
            return $this->view->fail("err_no_option");
        }
        $posting = isset($_POST['schedule_submit_' . $name]);
        if (!$posting || $this->currentUser === null || $readOnly) {
            $user = (!$readOnly && $this->currentUser !== null) ? $this->currentUser : null;
            $recs = $this->votingService->findAll($name, $user, (bool) $this->conf['sort_users']);
        } else {
            $submission = $this->submit($name, $options);
            $user = $this->currentUser;
            if ($submission !== null) {
                $this->votingService->vote($name, $user, $submission);
            }
            $recs = $this->votingService->findAll($name, $user, (bool) $this->conf['sort_users']);
        }
        return $this->planner($name, $options, $recs, $showTotals, $readOnly, $isMulti);
    }

    /**
     * @param array<bool|mixed> $args
     * @return array{bool,bool,bool,array<string>}
     */
    private function parseArguments(array $args): array
    {
        $showTotals = array_key_exists(0, $args) && is_bool($args[0])
            ? (bool) array_shift($args) : (bool) $this->conf['default_totals'];
        $readOnly = array_key_exists(0, $args) && is_bool($args[0])
            ? (bool) array_shift($args) : (bool) $this->conf['default_readonly'];
        $isMulti = array_key_exists(0, $args) && is_bool($args[0])
            ? (bool) array_shift($args) : (bool) $this->conf['default_multi'];
        $options = array_map("strval", $args);
        return [$showTotals, $readOnly, $isMulti, $options];
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
            }
        }
        $bag = [
            'showTotals'=> $showTotals,
            'currentUser' => $readOnly ? null : $this->currentUser,
            'url' => $this->url,
            'options' => $options,
            'counts' => $counts,
            'users' => $users,
            'itype' => $isMulti ? 'checkbox' : 'radio',
            'iname' => "schedule_date_$name",
            'sname' => "schedule_submit_$name",
            'columns' => count($options) + 1,
        ];
        return $this->view->render('planner', $bag);
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
}
