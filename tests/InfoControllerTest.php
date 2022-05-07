<?php

/**
 * Copyright 2022 Christoph M. Becker
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

final class InfoControllerTest extends TestCase
{
    public function testIt(): void
    {
        $view = $this->createMock(View::class);
        $view->expects($this->once())->method("render")->with("about", ["version" => "2.0-dev"]);
        $sut = new InfoController("2.0-dev", "", "", $view);
        ob_start();
        $sut->execute();
        ob_end_clean();
    }
}
