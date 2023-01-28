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

final class InfoController
{
    /** @var string */
    private $pluginVersion;

    /** @var string */
    private $pluginFolder;

    /** @var VotingService */
    private $votingService;

    /** @var View */
    private $view;

    /** @var SystemChecker */
    private $systemChecker;

    /** @var array<string,string> */
    private $lang;

    /**
     * @param array<string> $lang
     */
    public function __construct(
        string $pluginVersion,
        string $pluginFolder,
        VotingService $votingService,
        array $lang,
        SystemChecker $systemChecker
    ) {
        $this->pluginVersion = $pluginVersion;
        $this->pluginFolder = $pluginFolder;
        $this->votingService = $votingService;
        $this->view = new View("{$this->pluginFolder}views/", $lang);
        $this->lang = $lang;
        $this->systemChecker = $systemChecker;
    }

    public function execute(): string
    {
        return $this->view->render("info", [
            "version" => $this->pluginVersion,
            "checks" => $this->systemChecks(),
        ]);
    }

    /**
     * @return array<array{string,string}>
     */
    private function systemChecks(): array
    {
        $phpVersion = '7.1.0';
        $checks = [];
        $checks[] = [
            sprintf($this->lang['syscheck_phpversion'], $phpVersion),
            $this->systemChecker->checkVersion(PHP_VERSION, $phpVersion) >= 0 ? "xh_success" : "xh_fail",
        ];
        foreach (['session'] as $ext) {
            $checks[] = [
                sprintf($this->lang['syscheck_extension'], $ext),
                $this->systemChecker->checkExtension($ext) ? "xh_success" : "xh_fail",
            ];
        }
        foreach (['config/', 'css/', 'languages/'] as $folder) {
            $folders[] = "{$this->pluginFolder}$folder";
        }
        $folders[] = $this->votingService->dataFolder();
        foreach ($folders as $folder) {
            $checks[] = [
                sprintf($this->lang['syscheck_writable'], $folder),
                $this->systemChecker->checkWritability($folder) ? "xh_success" : "xh_warning",
            ];
        }
        return $checks;
    }
}
