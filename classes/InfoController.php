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
use Schedule\Infra\SystemChecker;
use Schedule\Infra\View;
use Schedule\Infra\VotingService;

final class InfoController
{
    /** @var VotingService */
    private $votingService;

    /** @var View */
    private $view;

    /** @var SystemChecker */
    private $systemChecker;

    public function __construct(
        VotingService $votingService,
        View $view,
        SystemChecker $systemChecker
    ) {
        $this->votingService = $votingService;
        $this->view = $view;
        $this->systemChecker = $systemChecker;
    }

    public function execute(Request $request): string
    {
        return $this->view->render("info", [
            "version" => SCHEDULE_VERSION,
            "checks" => $this->systemChecks($request->pluginsFolder()),
        ]);
    }

    /**
     * @return list<array{key:string,arg:string,class:string}>
     */
    private function systemChecks(string $pluginsFolder): array
    {
        $phpVersion = '7.1.0';
        $checks = [];
        $checks[] = [
            "key" => "syscheck_phpversion",
            "arg" => $phpVersion,
            "class" => $this->systemChecker->checkVersion(PHP_VERSION, $phpVersion) >= 0 ? "xh_success" : "xh_fail",
        ];
        foreach (['session'] as $ext) {
            $checks[] = [
                "key" => "syscheck_extension",
                "arg" => $ext,
                "class" => $this->systemChecker->checkExtension($ext) ? "xh_success" : "xh_fail",
            ];
        }
        foreach (['config/', 'css/', 'languages/'] as $folder) {
            $folders[] = $pluginsFolder . "realblog/" . $folder;
        }
        $folders[] = $this->votingService->dataFolder();
        foreach ($folders as $folder) {
            $checks[] = [
                "key" => "syscheck_writable",
                "arg" => $folder,
                "class" => $this->systemChecker->checkWritability($folder) ? "xh_success" : "xh_warning",
            ];
        }
        return $checks;
    }
}
