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

class CallBuilder
{
    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $config;

    /** @var View */
    private $view;

    /** @param array<string,string> $config */
    public function __construct(string $pluginFolder, array $config, View $view)
    {
        $this->pluginFolder = $pluginFolder;
        $this->config = $config;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        $js = $this->pluginFolder . "schedule.min.js";
        if (!is_file($js)) {
            $js = $this->pluginFolder . "schedule.js";
        }
        return Response::create($this->view->render("call_builder", [
            "js" => $request->url()->path($js)->with("v", SCHEDULE_VERSION)->relative(),
            "show_totals" => $this->config["default_totals"] ? "checked" : "",
            "read_only" => $this->config["default_readonly"] ? "checked" : "",
            "multi" => $this->config["default_multi"] ? "checked" : "",
        ]))->withTitle("Schedule – " . $this->view->text("menu_main"));
    }
}
