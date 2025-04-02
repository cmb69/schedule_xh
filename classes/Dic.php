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
use Plib\SystemChecker;
use Plib\View as PlibView;
use Schedule\Model\VoteRepo;

class Dic
{
    public static function makeInfoController(): InfoController
    {
        global $pth;

        return new InfoController(
            $pth["folder"]["plugins"] . "schedule/",
            self::makeDocumentStore(),
            self::makeView(),
            new SystemChecker()
        );
    }

    public static function callBuilder(): CallBuilder
    {
        global $pth, $plugin_cf;

        return new CallBuilder(
            $pth["folder"]["plugins"] . "schedule/",
            $plugin_cf["schedule"],
            self::makeView()
        );
    }

    public static function makeMainController(): MainController
    {
        global $plugin_cf;

        return new MainController(
            $plugin_cf["schedule"],
            self::makeDocumentStore(),
            self::makeView()
        );
    }

    private static function makeDocumentStore(): DocumentStore
    {
        global $pth, $cf, $sl;

        $folder = $pth['folder']['content'];
        if ($sl !== $cf['language']['default']) {
            $folder = dirname($folder) . "/";
        }
        return new DocumentStore($folder . "schedule/");
    }

    private static function makeView(): PlibView
    {
        global $plugin_tx, $pth;

        return new PlibView($pth["folder"]["plugins"] . "schedule/views/", $plugin_tx["schedule"]);
    }
}
