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

use Plib\SystemChecker;
use Plib\View as PlibView;
use Schedule\Infra\VoteRepo;

class Dic
{
    public static function makeInfoController(): InfoController
    {
        global $pth;

        return new InfoController(
            $pth["folder"]["plugins"] . "schedule/",
            self::makeVoteRepo(),
            self::makeView(),
            new SystemChecker()
        );
    }

    public static function makeMainController(): MainController
    {
        global $plugin_cf;

        return new MainController(
            $plugin_cf["schedule"],
            self::makeVoteRepo(),
            self::makeView()
        );
    }

    private static function makeVoteRepo(): VoteRepo
    {
        global $pth, $cf, $sl;

        $folder = $pth['folder']['content'];
        if ($sl !== $cf['language']['default']) {
            $folder = dirname($folder) . "/";
        }
        return new VoteRepo($folder);
    }

    private static function makeView(): PlibView
    {
        global $plugin_tx, $pth;

        return new PlibView($pth["folder"]["plugins"] . "schedule/views/", $plugin_tx["schedule"]);
    }
}
