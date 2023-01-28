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

class Dic
{
    public static function makeInfoController(): InfoController
    {
        global $pth, $plugin_tx;

        return new InfoController(
            SCHEDULE_VERSION,
            "{$pth['folder']['plugins']}schedule/",
            self::makeVotingService(),
            $plugin_tx['schedule'],
            new SystemChecker()
        );
    }

    public static function makeMainController(): MainController
    {
        global $pth, $sn, $su, $plugin_cf, $plugin_tx;

        return new MainController(
            $plugin_cf["schedule"],
            "$sn?$su",
            self::makeVotingService(),
            "{$pth['folder']['plugins']}schedule/",
            $plugin_tx['schedule'],
            $_SESSION['username'] ?? ($_SESSION['Name'] ?? null)
        );
    }

    private static function makeVotingService(): VotingService
    {
        global $pth, $cf, $sl;

        return new VotingService($pth['folder']['content'], $sl === $cf['language']['default']);
    }
}
