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

use PHPUnit\Framework\TestCase;

class DicTest extends TestCase
{
    public function setUp(): void
    {
        global $cf, $plugin_cf, $plugin_tx, $pth;

        $cf = ["language" => ["default" => ""]];
        $plugin_cf = ["schedule" => []];
        $plugin_tx = ["schedule" => []];
        $pth = ["folder" => ["content" => "", "plugins" => ""]];
    }

    public function testMakesMainController(): void
    {
        $this->assertInstanceOf(MainController::class, Dic::makeMainController());
    }

    public function testMakesInfoController(): void
    {
        $this->assertInstanceOf(InfoController::class, Dic::makeInfoController());
    }
}
