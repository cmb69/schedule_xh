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

class FakeRequest extends Request
{
    private $options;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    public function url(): string
    {
        return "http://example.com/?Schedule";
    }

    public function user(): ?string
    {
        return $this->options["user"] ?? null;
    }

    protected function post()
    {
        return $this->options["post"] ?? [];
    }

    protected function pth(): array
    {
        return $this->options["pth"] ?? [];
    }
}
