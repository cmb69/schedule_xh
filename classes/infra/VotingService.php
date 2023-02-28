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

class VotingService
{
    /** @var string */
    private $contentFolder;

    /** @var bool */
    private $isMainLanguage;

    public function __construct(string $contentFolder, bool $isMainLanguage)
    {
        $this->contentFolder = $contentFolder;
        $this->isMainLanguage = $isMainLanguage;
    }

    public function dataFolder(): string
    {
        $fn = $this->contentFolder;
        if (!$this->isMainLanguage) {
            $fn = dirname($fn) . "/";
        }
        $fn .= "schedule";
        if (!file_exists($fn)) {
            if (mkdir($fn, 0777, true)) {
                chmod($fn, 0777);
            }
        }
        $fn .= "/";
        return $fn;
    }

    /**
     * @return list<Vote>
     */
    public function findAll(string $name, ?string $user, bool $sorted): array
    {
        $filename = "{$this->dataFolder()}{$name}.csv";
        if (!is_readable($filename)) {
            return [];
        }
        $file = fopen($filename, "r");
        assert($file !== false);
        flock($file, LOCK_SH);
        $votes = [];
        $voted = false;
        while (($record = $this->readRecord($file)) !== false) {
            if ($record[0] !== null) {
                assert($this->containsOnlyStrings($record));
                $votes[] = new Vote($record[0], array_slice($record, 1));
                if ($record[0] === $user) {
                    $voted = true;
                }
            }
        }
        flock($file, LOCK_UN);
        fclose($file);
        if ($user !== null && !$voted) {
            $votes[] = new Vote($user, []);
        }
        if ($sorted) {
            usort($votes, function ($a, $b) {
                return $a->voter() <=> $b->voter();
            });
        }
        return $votes;
    }

    public function vote(string $name, Vote $vote): bool
    {
        $filename = "{$this->dataFolder()}{$name}.csv";
        if (!is_writeable($filename)) {
            return false;
        }
        $file = fopen($filename, "c+");
        assert($file !== false);
        flock($file, LOCK_EX);
        $temp = fopen("php://temp", "w+");
        if ($temp === false) {
            return false;
        }
        while (($record = $this->readRecord($file)) !== false) {
            if ($record[0] !== null) {
                assert($this->containsOnlyStrings($record));
                if ($record[0] !== $vote->voter()) {
                    fputcsv($temp, $record, "\t", "\"", "\0");
                }
            }
        }
        fputcsv($temp, array_merge([$vote->voter()], $vote->choices()), "\t", "\"", "\0");
        rewind($temp);
        rewind($file);
        stream_copy_to_stream($temp, $file);
        fclose($temp);
        $pos = ftell($file);
        assert($pos !== false && $pos > 0);
        ftruncate($file, $pos);
        flock($file, LOCK_UN);
        fclose($file);
        return true;
    }

    /**
     * @param resource $stream
     * @return array<string|null>|false
     */
    private function readRecord($stream)
    {
        $result = fgetcsv($stream, 0, "\t", "\"", "\0");
        assert($result !== null);
        return $result;
    }

    /**
     * @param array<string|null> $record
     * @phpstan-assert-if-true array<string> $record
     */
    private function containsOnlyStrings(array $record): bool
    {
        foreach ($record as $field) {
            if (!is_string($field)) {
                return false;
            }
        }
        return true;
    }
}
