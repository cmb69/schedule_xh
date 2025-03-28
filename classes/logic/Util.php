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

use Schedule\Value\Vote;

class Util
{
    /** @param list<Vote> $votes */
    public static function hasVoted(string $user, array $votes): bool
    {
        foreach ($votes as $vote) {
            if ($vote->voter() === $user) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param list<Vote> $votes
     * @return list<Vote>
     */
    public static function sortVotesByVoter(array $votes): array
    {
        usort($votes, function ($a, $b) {
            return $a->voter() <=> $b->voter();
        });
        return array_values($votes);
    }
}
