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

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use Schedule\Value\Vote;

final class VoteRepoTest extends TestCase
{
    /** @var vfsStreamDirectory */
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('home');
    }

    public function testFindNothing(): void
    {
        $sut = new VoteRepo($this->root->url() . "/");
        $actual = $sut->findAll("test");
        $this->assertEquals($actual, []);
    }

    public function testFindAll(): void
    {
        $this->csvFixture();
        $sut = new VoteRepo($this->root->url() . "/");
        $actual = $sut->findAll("test");
        $expected = [
            new Vote("userA", ["optA"]),
            new Vote("userB", ["optB"]),
        ];
        $this->assertEquals($actual, $expected);
    }

    public function testFindsAllWithoutDuplicate(): void
    {
        $this->csvFixture();
        $sut = new VoteRepo($this->root->url() . "/");
        $actual = $sut->findAll("test");
        $expected = [
            new Vote("userA", ["optA"]),
            new Vote("userB", ["optB"]),
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testVote(): void
    {
        $file = $this->csvFixture();
        $sut = new VoteRepo($this->root->url() . "/");
        $sut->save("test", new Vote("userC", ["optC"]));
        $expected =
            "userA\toptA\n"
            . "userB\toptB\n"
            . "userC\toptC\n";
        $this->assertEquals($expected, $file->getContent());
    }

    public function testVoteAgain(): void
    {
        $file = $this->csvFixture();
        $sut = new VoteRepo($this->root->url() . "/");
        $sut->save("test", new Vote("userA", ["optC"]));
        $expected =
            "userB\toptB\n"
            . "userA\toptC\n";
        $this->assertEquals($expected, $file->getContent());
    }

    private function csvFixture(): vfsStreamFile
    {
        return vfsStream::newFile("schedule/test.csv")->at($this->root)->setContent(
            "userA\toptA\n"
            . "userB\toptB\n"
        );
    }
}
