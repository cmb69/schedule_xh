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

use Plib\Request;
use Plib\Response;
use Plib\View;
use Schedule\Model\Arguments;
use Schedule\Model\Vote;
use Schedule\Model\VoteRepo;

final class MainController
{
    /** @var array<string,string> */
    private $conf;

    /** @var VoteRepo */
    private $voteRepo;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        array $conf,
        VoteRepo $voteRepo,
        View $view
    ) {
        $this->conf = $conf;
        $this->voteRepo = $voteRepo;
        $this->view = $view;
    }

    /** @param bool|string $args */
    public function __invoke(Request $request, string $name, ...$args): Response
    {
        if (!preg_match('/^[a-z\-0-9]+$/i', $name)) {
            return Response::create($this->view->message("fail", "err_invalid_name"));
        }
        if (($args = $this->parseArguments(array_values($args))) === null) {
            return Response::create($this->view->message("fail", "err_no_option"));
        }
        if ($this->postFor($request, $name) !== null) {
            return $this->vote($request, $name, $args);
        }
        return Response::create($this->widget($request, $name, $args));
    }

    private function widget(Request $request, string $name, Arguments $args): string
    {
        $user = (!$args->readOnly() && $request->username() !== null) ? $request->username() : null;
        $votes = $this->voteRepo->findAll($name);
        if ($user && !$this->hasVoted($user, $votes)) {
            $votes[] = new Vote($user, []);
        }
        if ($this->conf["sort_users"]) {
            $votes = $this->sortVotesByVoter($votes);
        }
        return $this->renderWidget($request, $name, $args, $votes);
    }

    /** @param list<Vote> $votes */
    private function hasVoted(string $user, array $votes): bool
    {
        foreach ($votes as $vote) {
            if ($vote->voter() === $user) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param list<Vote> $votes
     * @return list<Vote>
     */
    private function sortVotesByVoter(array $votes): array
    {
        usort($votes, function ($a, $b) {
            return $a->voter() <=> $b->voter();
        });
        return array_values($votes);
    }

    private function vote(Request $request, string $name, Arguments $args): Response
    {
        if ($request->username() === null || $args->readOnly()) {
            return Response::create($this->view->message("fail", "err_vote")
                . $this->widget($request, $name, $args));
        }
        if (($vote = $this->parseVote($request, $name, $args->options())) === null) {
            return Response::create($this->view->message("fail", "err_vote")
                . $this->widget($request, $name, $args));
        }
        if (!$this->voteRepo->save($name, $vote)) {
            return Response::create($this->view->message("fail", "err_save")
                . $this->widget($request, $name, $args));
        }
        return Response::redirect($request->url()->absolute());
    }

    /** @param list<bool|string> $args */
    private function parseArguments(array $args): ?Arguments
    {
        $args = array_map(function ($arg) {
            return !is_string($arg) ? $arg : html_entity_decode($arg, ENT_QUOTES, "UTF-8");
        }, $args);
        return Arguments::parse($args, [
            "totals" => (bool) $this->conf["default_totals"],
            "readonly" => (bool) $this->conf["default_readonly"],
            "multi" => (bool) $this->conf["default_multi"],
        ]);
    }

    /** @param list<string> $options */
    private function parseVote(Request $request, string $name, array $options): ?Vote
    {
        assert($request->username() !== null);
        assert($this->postFor($request, $name) !== null);
        $fields = $this->postFor($request, $name)["dates"];
        $choices = [];
        foreach ($fields as $field) {
            if (array_search($field, $options) === false) {
                // user voted for invalid option, what's normally not possible
                return null;
            }
            $choices[] = $field;
        }
        return new Vote($request->username(), $choices);
    }

    /** @param list<Vote> $votes */
    private function renderWidget(Request $request, string $name, Arguments $args, array $votes): string
    {
        return $this->view->render("planner", [
            "show_totals" => $args->totals(),
            "voting" => $args->readonly() ? null : $request->username(),
            "url" => $request->url()->relative(),
            "options" => $args->options(),
            "totals" => $this->totals($args, $votes),
            "users" => $this->users($request, $name, $args, $votes),
            "button" => "schedule_submit_$name",
            "columns" => count($args->options()) + 1,
        ]);
    }

    /**
     * @param list<Vote> $votes
     * @return list<int>
     */
    private function totals(Arguments $args, array $votes): array
    {
        $totals = array_fill(0, count($args->options()), 0);
        foreach ($votes as $vote) {
            foreach ($args->options() as $i => $option) {
                $totals[$i] += (int) in_array($option, $vote->choices(), true);
            }
        }
        return $totals;
    }

    /**
     * @param list<Vote> $votes
     * @return array<string,list<array{class:string,content:string}>>
     */
    private function users(Request $request, string $name, Arguments $args, array $votes): array
    {
        $users = [];
        foreach ($votes as $vote) {
            $users[$vote->voter()] = [];
            foreach ($args->options() as $option) {
                $ok = in_array($option, $vote->choices(), true);
                $users[$vote->voter()][] = [
                    "class" => $ok ? "schedule_green" : "schedule_red",
                    "content" => $vote->voter() === $request->username()
                        ? $this->input($name, $args, $option, $ok)
                        : ($ok ? $this->view->text("label_checked") : $this->view->text("label_unchecked")),
                ];
            }
        }
        return $users;
    }

    /** @return array{submit:string,dates:list<string>}|null */
    public function postFor(Request $request, string $name): ?array
    {
        $submit = $request->post("schedule_submit_$name");
        $dates = $request->postArray("schedule_date_$name");
        if ($submit === null) {
            return null;
        }
        return ["submit" => $submit, "dates" => array_values($dates ?? [])];
    }

    private function input(string $name, Arguments $args, string $option, bool $checked): string
    {
        $type = $args->multi() ? "checkbox" : "radio";
        $option = $this->view->esc($option);
        $checked = $checked ? " checked" : "";
        return "<input type=\"{$type}\" name=\"schedule_date_{$name}[]\" value=\"{$option}\"{$checked}>";
    }
}
