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

use Plib\CsrfProtector;
use Plib\DocumentStore;
use Plib\Request;
use Plib\Response;
use Plib\View;
use Schedule\Model\Arguments;
use Schedule\Model\Voting;

final class MainController
{
    /** @var array<string,string> */
    private $conf;

    /** @var DocumentStore */
    private $store;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        array $conf,
        DocumentStore $store,
        CsrfProtector $csrfProtector,
        View $view
    ) {
        $this->conf = $conf;
        $this->store = $store;
        $this->csrfProtector = $csrfProtector;
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
        $voting = $this->store->retrieve($name . ".csv", Voting::class);
        assert($voting instanceof Voting); // won't return null
        if ($user && !$voting->voted($user)) {
            $voting->vote($user, []);
        }
        if ($this->conf["sort_users"]) {
            $voting->sort();
        }
        return $this->renderWidget($request, $name, $args, $voting);
    }

    private function vote(Request $request, string $name, Arguments $args): Response
    {
        if ($request->username() === null || $args->readOnly()) {
            return Response::create($this->view->message("fail", "err_vote")
                . $this->widget($request, $name, $args));
        }
        if (!$this->csrfProtector->check($request->post("schedule_token"))) {
            return Response::create($this->view->message("fail", "err_vote")
                . $this->widget($request, $name, $args));
        }
        if (($choices = $this->parseVote($request, $name, $args->options())) === null) {
            return Response::create($this->view->message("fail", "err_vote")
                . $this->widget($request, $name, $args));
        }
        $voting = $this->store->update($name . ".csv", Voting::class);
        assert($voting instanceof Voting); // won't return null
        $voting->vote($request->username(), $choices);
        if (!$this->store->commit($voting)) {
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

    /**
     * @param list<string> $options
     * @return ?list<string>
     */
    private function parseVote(Request $request, string $name, array $options): ?array
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
        return $choices;
    }

    private function renderWidget(Request $request, string $name, Arguments $args, Voting $voting): string
    {
        return $this->view->render("planner", [
            "show_totals" => $args->totals(),
            "voting" => $args->readonly() ? null : $request->username(),
            "url" => $request->url()->relative(),
            "options" => $args->options(),
            "totals" => $this->totals($args, $voting),
            "users" => $this->users($request, $name, $args, $voting),
            "button" => "schedule_submit_$name",
            "columns" => count($args->options()) + 1,
            "csrf_token" => $this->csrfProtector->token(),
        ]);
    }

    /** @return list<int> */
    private function totals(Arguments $args, Voting $voting): array
    {
        $totals = array_fill(0, count($args->options()), 0);
        foreach ($voting->votes() as $choices) {
            foreach ($args->options() as $i => $option) {
                $totals[$i] += (int) in_array($option, $choices, true);
            }
        }
        return $totals;
    }

    /** @return array<string,list<array{class:string,content:string}>> */
    private function users(Request $request, string $name, Arguments $args, Voting $voting): array
    {
        $users = [];
        foreach ($voting->votes() as $voter => $choices) {
            $users[$voter] = [];
            foreach ($args->options() as $option) {
                $ok = in_array($option, $choices, true);
                $users[$voter][] = [
                    "class" => $ok ? "schedule_green" : "schedule_red",
                    "content" => $voter === $request->username()
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
