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

use Schedule\Value\Vote;

class FakeVoteRepo extends VoteRepo
{
    private $votes = [];

    public function __construct() {}

    public function dataFolder(): string
    {
        return "./content/schedule/";
    }

    public function findAll(string $topic): array
    {
        return $this->votes[$topic] ?? [];
    }

    public function save(string $topic, Vote $vote): bool
    {
        $this->votes[$topic][$vote->voter()] = $vote;
        return true;
    }
}
