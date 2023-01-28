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

class View
{
    /** @var string */
    private $folder;

    /** @var array<string,string> */
    private $lang;

    /**
     * @param array<string,string> $lang
     */
    public function __construct(string $folder, array $lang)
    {
        $this->folder = $folder;
        $this->lang = $lang;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function render(string $template, array $data): string
    {
        extract($data);
        ob_start();
        include "$this->folder/$template.php";
        return (string) ob_get_clean();
    }

    /**
     * @param mixed $args
     */
    public function text(string $key, ...$args): string
    {
        return vsprintf($this->lang[$key], $args);
    }

    public function fail(string $key): string
    {
        return "<p class=\"xh_fail\">{$this->text($key)}</p>";
    }
}
