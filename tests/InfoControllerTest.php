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

use function XH_includeVar;

use PHPUnit\Framework\TestCase;

final class InfoControllerTest extends TestCase
{
    public function testIt(): void
    {
        $plugin_tx = XH_includeVar("./languages/en.php", "plugin_tx");
        assert(is_array($plugin_tx));
        $sut = new InfoController("2.0-dev", "", "", $plugin_tx['schedule'], new SystemChecker());
        $sut->execute();
    }
}
