<?php

/**
 * Copyright 2022 Christoph M. Becker
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

final class VotingService
{
    /** @var string */
    private $folder;

    public function __construct(string $folder)
    {
        $this->folder = $folder;
    }

    /**
     * @return array<string,array<string>>
     */
    public function findAll(string $name, ?string $user, bool $sorted): array
    {
        $filename = "{$this->folder}/{$name}.csv";
        if (!is_readable($filename)) {
            return [];
        }
        $file = fopen($filename, "r");
        flock($file, LOCK_SH);
        $records = [];
        while (($record = fgetcsv($file, 0, "\t", "\"", "\0")) !== false) {
            $records[$record[0]] = array_slice($record, 1);
        }
        flock($file, LOCK_UN);
        fclose($file);
        if ($user !== null && !array_key_exists($user, $records)) {
            $records[$user] = [];
        }
        if ($sorted) {
            ksort($records);
        }
        return $records;
    }

    /**
     * @param array<string> $options
     */
    public function vote(string $name, string $user, array $options): void
    {
        $filename = "{$this->folder}/{$name}.csv";
        $file = fopen($filename, "c+");
        flock($file, LOCK_EX);
        $temp = fopen("php://temp", "w+");
        while (($record = fgetcsv($file, 0, "\t", "\"", "\0")) !== false) {
            if ($record[0] !== $user) {
                fputcsv($temp, $record, "\t", "\"", "\0");
            }
        }
        fputcsv($temp, array_merge([$user], $options), "\t", "\"", "\0");
        rewind($temp);
        rewind($file);
        stream_copy_to_stream($temp, $file);
        fclose($temp);
        ftruncate($file, ftell($file));
        flock($file, LOCK_UN);
        fclose($file);
    }
}
