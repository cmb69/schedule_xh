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

namespace Schedule\Logic;

use Schedule\Value\Arguments;

class Util
{
    /**
     * @param array<bool|mixed> $args
     * @param array{totals:bool,readonly:bool,multi:bool} $defaults
     */
    public static function parseArguments(array $args, array $defaults): ?Arguments
    {
        $showTotals = array_key_exists(0, $args) && is_bool($args[0])
            ? (bool) array_shift($args) : $defaults["totals"];
        $readOnly = array_key_exists(0, $args) && is_bool($args[0])
            ? (bool) array_shift($args) : $defaults["readonly"];
        $isMulti = array_key_exists(0, $args) && is_bool($args[0])
            ? (bool) array_shift($args) : $defaults["multi"];
        $options = array_map("strval", $args);
        if (empty($options)) {
            return null;
        }
        return new Arguments($showTotals, $readOnly, $isMulti, $options);
    }
}
