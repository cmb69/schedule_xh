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

use Schedule\Infra\Request;
use Schedule\Infra\View;
use Schedule\Infra\VotingService;
use Schedule\Logic\Util;
use Schedule\Value\Arguments;

final class MainController
{
    /** @var array<string,string> */
    private $conf;

    /** @var VotingService */
    private $votingService;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        array $conf,
        VotingService $votingService,
        View $view
    ) {
        $this->conf = $conf;
        $this->votingService = $votingService;
        $this->view = $view;
    }

    /** @param bool|string $args */
    public function __invoke(Request $request, string $name, ...$args): string
    {
        if (!preg_match('/^[a-z\-0-9]+$/i', $name)) {
            return $this->view->fail("err_invalid_name");
        }
    
        $args = Util::parseArguments($args, [
            "totals" => (bool) $this->conf['default_totals'],
            "readonly" => (bool) $this->conf['default_readonly'],
            "multi" => (bool) $this->conf['default_multi'],
        ]);
        if (empty($args->options())) {
            return $this->view->fail("err_no_option");
        }

        $posting = isset($_POST['schedule_submit_' . $name]);
        if (!$posting || $request->user() === null || $args->readOnly()) {
            $user = (!$args->readOnly() && $request->user() !== null) ? $request->user() : null;
            $recs = $this->votingService->findAll($name, $user, (bool) $this->conf['sort_users']);
        } else {
            $submission = $this->submit($name, $args->options());
            $user = $request->user();
            if ($submission !== null) {
                $this->votingService->vote($name, $user, $submission);
            }
            $recs = $this->votingService->findAll($name, $user, (bool) $this->conf['sort_users']);
        }
        return $this->planner($name, $args, $recs, $request);
    }

    /** @param array<string,array<string>> $recs */
    private function planner(
        string $name,
        Arguments $args,
        array $recs,
        Request $request
    ): string {
        $counts = [];
        foreach ($args->options() as $option) {
            $counts[$option] = 0;
        }
        $users = [];
        $cells = [];
        foreach ($recs as $user => $rec) {
            $users[$user] = [];
            $cells[$user] = [];
            foreach ($args->options() as $option) {
                $ok = array_search($option, $rec) !== false;
                $users[$user][$option] = $ok ? "schedule_green" : "schedule_red";
                if ($ok) {
                    $counts[$option]++;
                }
            }
        }
        $bag = [
            'showTotals'=> $args->totals(),
            'currentUser' => $args->readonly() ? null : $request->user(),
            'url' => $request->url(),
            'options' => $args->options(),
            'counts' => $counts,
            'users' => $users,
            'itype' => $args->multi() ? 'checkbox' : 'radio',
            'iname' => "schedule_date_$name",
            'sname' => "schedule_submit_$name",
            'columns' => count($args->options()) + 1,
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
