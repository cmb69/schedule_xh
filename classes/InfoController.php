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

use Plib\DocumentStore;
use Plib\Response;
use Plib\SystemChecker;
use Plib\View;

final class InfoController
{
    /** @var string */
    private $pluginFolder;

    /** @var DocumentStore */
    private $store;

    /** @var View */
    private $view;

    /** @var SystemChecker */
    private $systemChecker;

    public function __construct(
        string $pluginFolder,
        DocumentStore $store,
        View $view,
        SystemChecker $systemChecker
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->store = $store;
        $this->view = $view;
        $this->systemChecker = $systemChecker;
    }

    public function __invoke(): Response
    {
        return Response::create($this->view->render("info", [
            "version" => SCHEDULE_VERSION,
            "checks" => $this->systemChecks(),
        ]))->withTitle("Schedule " . SCHEDULE_VERSION);
    }

    /**
     * @return list<object{key:string,arg:string,class:string}>
     */
    private function systemChecks(): array
    {
        $phpVersion = '7.1.0';
        $checks = [];
        $checks[] = (object) [
            "key" => "syscheck_phpversion",
            "arg" => $phpVersion,
            "class" => $this->systemChecker->checkVersion(PHP_VERSION, $phpVersion) ? "xh_success" : "xh_fail",
        ];
        $xhVersion = "1.7.0";
        $okay = $this->systemChecker->checkVersion(CMSIMPLE_XH_VERSION, "CMSimple_XH $xhVersion");
        $checks[] = (object) [
            "key" => "syscheck_xhversion",
            "arg" => $xhVersion,
            "class" => $okay ? "xh_success" : "xh_fail",
        ];
        $plibVersion = "1.6";
        $okay = $this->systemChecker->checkPlugin("plib", $plibVersion);
        $checks[] = (object) [
            "key" => "syscheck_plibversion",
            "arg" => $plibVersion,
            "class" => $okay ? "xh_success" : "xh_fail",
        ];
        foreach (['config/', 'css/', 'languages/'] as $folder) {
            $folders[] = $this->pluginFolder . $folder;
        }
        $folders[] = $this->store->folder();
        foreach ($folders as $folder) {
            $checks[] = (object) [
                "key" => "syscheck_writable",
                "arg" => $folder,
                "class" => $this->systemChecker->checkWritability($folder) ? "xh_success" : "xh_warning",
            ];
        }
        return $checks;
    }
}
