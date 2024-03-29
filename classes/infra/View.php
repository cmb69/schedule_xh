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

namespace Schedule\Infra;

class View
{
    /** @var string */
    private $folder;

    /** @var array<string,string> */
    private $text;

    /** @param array<string,string> $text */
    public function __construct(string $folder, array $text)
    {
        $this->folder = $folder;
        $this->text = $text;
    }

    /** @param array<string,mixed> $data */
    public function render(string $template, array $data): string
    {
        extract($data);
        ob_start();
        include "{$this->folder}$template.php";
        return (string) ob_get_clean();
    }

    /** @param string|int $args */
    public function text(string $key, ...$args): string
    {
        return vsprintf($this->esc($this->text[$key]), array_map([$this, 'esc'], $args));
    }

    public function fail(string $key): string
    {
        return "<p class=\"xh_fail\">{$this->text($key)}</p>\n";
    }

    /** @param string|int $value */
    public function esc($value): string
    {
        return XH_hsc((string) $value);
    }

    public function raw(string $string): string
    {
        return $string;
    }
}
