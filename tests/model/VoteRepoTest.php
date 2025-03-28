<?php

namespace Schedule\Model;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

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
