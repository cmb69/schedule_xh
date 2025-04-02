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

namespace Schedule\Model;

use Plib\Document;

final class Voting implements Document
{
    /** @var array<string,list<string>> */
    private $votes;

    /** @return static */
    public static function fromString(string $contents)
    {
        $data = [];
        if ($contents !== "") {
            $lines = explode("\n", $contents);
            foreach ($lines as $line) {
                $record = str_getcsv(rtrim($line), "\t", '"', "\0");
                if (self::assertAllStrings($record)) {
                    $data[$record[0]] = array_slice($record, 1);
                }
            }
        }
        $that = new static();
        $that->votes = $data;
        return $that;
    }

    /**
     * @param list<string|null> $record
     * @phpstan-assert-if-true list<string> $record
     */
    private static function assertAllStrings($record): bool
    {
        foreach ($record as $fields) {
            if (!is_string($fields)) {
                return false;
            }
        }
        return true;
    }

    public function toString(): string
    {
        $lines = [];
        foreach ($this->votes as $voter => $choices) {
            array_unshift($choices, $voter);
            $lines[] = implode("\t", $choices);
        }
        return implode("\n", $lines);
    }

    private function __construct()
    {
    }

    /** @return array<string,list<string>> */
    public function votes(): array
    {
        return $this->votes;
    }

    public function voted(string $user): bool
    {
        return isset($this->votes[$user]);
    }

    /** @param list<string> $choices */
    public function vote(string $user, array $choices): void
    {
        $this->votes[$user] = $choices;
    }

    public function sort(): void
    {
        ksort($this->votes);
    }
}
