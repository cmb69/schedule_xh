<?php

/**
 * Copyright 2012-2022 Christoph M. Becker
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

/**
 * Autoloads the plugin classes.
 *
 * @param string $class A class name.
 *
 * @return void
 */
function Schedule_autoload($class)
{
    global $pth;
    $parts = explode('_', $class, 2);
    if ($parts[0] == 'Schedule') {
        include_once $pth['folder']['plugins'] . 'schedule/classes/'
            . $parts[1] . '.php';
    }
}

spl_autoload_register('Schedule_autoload');
