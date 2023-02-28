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

namespace Schedule\Value;

class Arguments
{
    /** @var bool */
    private $totals;

    /** @var bool */
    private $readonly;

    /** @var bool */
    private $multi;

    /** @var list<string> */
    private $options;

    /** @param list<string> $options */
    public function __construct(bool $totals, bool $readonly, bool $multi, array $options)
    {
        $this->totals = $totals;
        $this->readonly = $readonly;
        $this->multi = $multi;
        $this->options = $options;
    }

    public function totals(): bool
    {
        return $this->totals;
    }

    public function readonly(): bool
    {
        return $this->readonly;
    }

    public function multi(): bool
    {
        return $this->multi;
    }

    /** @return list<string> */
    public function options(): array
    {
        return $this->options;
    }
}
