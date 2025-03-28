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

class Request
{
    /** @codeCoverageIgnore */
    public function url(): string
    {
        global $sn, $su;
        return $sn . "?" . $su;
    }

    /** @codeCoverageIgnore */
    public function user(): ?string
    {
        return $_SESSION['username'] ?? ($_SESSION['Name'] ?? null);
    }

    /** @return array{submit:string,dates:list<string>}|null */
    public function postFor(string $name): ?array
    {
        $post = $this->post();
        $submit = $post["schedule_submit_$name"] ?? null;
        $dates = $post["schedule_date_$name"] ?? null;
        if ($submit === null || !is_string($submit) || ($dates !== null && !is_array($dates))) {
            return null;
        }
        return ["submit" => $submit, "dates" => array_values($dates ?? [])];
    }

    /**
     * @codeCoverageIgnore
     * @return array<string,string|array<string,string>>
     */
    protected function post()
    {
        return $_POST;
    }
}
