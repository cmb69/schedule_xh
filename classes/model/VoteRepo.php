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

class VoteRepo
{
    /** @var string */
    private $contentFolder;

    public function __construct(string $contentFolder)
    {
        $this->contentFolder = $contentFolder;
    }

    public function dataFolder(): string
    {
        $foldername = $this->contentFolder . "schedule";
        if (!file_exists($foldername) && mkdir($foldername, 0777, true)) {
            chmod($foldername, 0777);
        }
        $foldername .= "/";
        return $foldername;
    }

    /** @return list<Vote> */
    public function findAll(string $topic): array
    {
        $filename = $this->dataFolder() . $topic . ".csv";
        if (!is_readable($filename) || ($file = fopen($filename, "r")) === false) {
            return [];
        }
        flock($file, LOCK_SH);
        $votes = [];
        while (($record = $this->readCsvRecord($file)) !== null) {
            $votes[] = new Vote($record[0], array_slice($record, 1));
        }
        flock($file, LOCK_UN);
        fclose($file);
        return $votes;
    }

    public function save(string $topic, Vote $vote): bool
    {
        $filename = $this->dataFolder() . $topic . ".csv";
        if (!is_writeable($filename) || ($file = fopen($filename, "c+")) === false) {
            return false;
        }
        flock($file, LOCK_EX);
        if (($temp = fopen("php://temp", "w+")) === false) {
            return false;
        }
        while (($record = $this->readCsvRecord($file)) !== null) {
            if ($record[0] !== $vote->voter()) {
                if (!$this->writeCsvRecord($temp, $record)) {
                    return false;
                }
            }
        }
        $record = array_merge([$vote->voter()], $vote->choices());
        if (!$this->writeCsvRecord($temp, $record)) {
            return false;
        }
        if (!rewind($temp) || !rewind($file) || !stream_copy_to_stream($temp, $file)) {
            return false;
        }
        fclose($temp);
        if (($pos = ftell($file)) !== false) {
            assert($pos >= 0);
            ftruncate($file, $pos);
        }
        flock($file, LOCK_UN);
        fclose($file);
        return true;
    }

    /**
     * @param resource $stream
     * @return list<string>|null
     */
    private function readCsvRecord($stream): ?array
    {
        $result = fgetcsv($stream, 0, "\t", "\"", "\0");
        assert($result !== null); // I don't think this can happen
        if ($result === false) {
            return null;
        }
        return array_filter($result);
    }

    /**
     * @param resource $stream
     * @param list<string> $record
     */
    private function writeCsvRecord($stream, $record): bool
    {
        return fputcsv($stream, $record, "\t", "\"", "\0") !== false;
    }
}
