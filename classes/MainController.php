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
use Schedule\Value\Vote;

final class MainController
{
    /** @var array<string,string> */
    private $conf;

    /** @var VotingService */
    private $votingService;

    /** @var View */
    private $view;

    /** @var Request */
    private $request;

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
        $this->request = $request;

        if (!preg_match('/^[a-z\-0-9]+$/i', $name)) {
            return $this->view->fail("err_invalid_name");
        }
        if (($args = $this->parseArguments($args)) === null) {
            return $this->view->fail("err_no_option");
        }

        $posting = isset($_POST["schedule_submit_" . $name]);
        if (!$posting || $this->request->user() === null || $args->readOnly()) {
            $user = (!$args->readOnly() && $this->request->user() !== null) ? $this->request->user() : null;
            $votes = $this->votingService->findAll($name, $user, (bool) $this->conf["sort_users"]);
        } else {
            $vote = $this->parseVote($name, $args->options());
            if ($vote !== null) {
                $this->votingService->vote($name, $vote);
            }
            $votes = $this->votingService->findAll($name, $this->request->user(), (bool) $this->conf["sort_users"]);
        }
        return $this->renderWidget($name, $args, $votes);
    }

    /** @param list<bool|string> $args */
    private function parseArguments(array $args): ?Arguments
    {
        $args = array_map(function ($arg) {
            return !is_string($arg) ? $arg : html_entity_decode($arg, ENT_QUOTES, "UTF-8");
        }, $args);
        return Util::parseArguments($args, [
            "totals" => (bool) $this->conf["default_totals"],
            "readonly" => (bool) $this->conf["default_readonly"],
            "multi" => (bool) $this->conf["default_multi"],
        ]);
    }

    /** @param list<Vote> $votes */
    private function renderWidget(string $name, Arguments $args, array $votes): string
    {
        $counts = [];
        foreach ($args->options() as $option) {
            $counts[$option] = 0;
        }
        $users = [];
        $cells = [];
        foreach ($votes as $vote) {
            $users[$vote->voter()] = [];
            $cells[$vote->voter()] = [];
            foreach ($args->options() as $option) {
                $ok = array_search($option, $vote->choices()) !== false;
                $users[$vote->voter()][$option] = $ok ? "schedule_green" : "schedule_red";
                if ($ok) {
                    $counts[$option]++;
                }
            }
        }
        return $this->view->render("planner", [
            "showTotals"=> $args->totals(),
            "currentUser" => $args->readonly() ? null : $this->request->user(),
            "url" => $this->request->url(),
            "options" => $args->options(),
            "counts" => $counts,
            "users" => $users,
            "itype" => $args->multi() ? "checkbox" : "radio",
            "iname" => "schedule_date_$name",
            "sname" => "schedule_submit_$name",
            "columns" => count($args->options()) + 1,
        ]);
    }

    /** @param list<string> $options */
    private function parseVote(string $name, array $options): ?Vote
    {
        assert($this->request->user() !== null);
        $fields = $_POST["schedule_date_" . $name] ?? [];
        $choices = [];
        foreach ($fields as $field) {
            if (array_search($field, $options) === false) {
                // user voted for invalid option, what's normally not possible
                return null;
            }
            $choices[] = $field;
        }
        return new Vote($this->request->user(), $choices);
    }
}
