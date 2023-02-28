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

class Response
{
    public static function create(string $output): self
    {
        $that = new Response;
        $that->output = $output;
        return $that;
    }

    public static function redirect(string $location): self
    {
        $that = new Response;
        $that->location = $location;
        return $that;
    }

    /** @var string */
    private $output = "";

    /** @var string|null */
    private $location = null;

    public function merge(Response $other): self
    {
        $that = clone $this;
        $that->output .= $other->output;
        assert($this->location === null); // must not merge into redirect response
        $that->location = $other->location;
        return $that;
    }

    public function output(): string
    {
        return $this->output;
    }

    public function location(): ?string
    {
        return $this->location;
    }

    public function fire(): string
    {
        if ($this->location !== null) {
            header("Location: " . $this->location, true, 303);
            exit;
        }
        return $this->output;
    }
}
