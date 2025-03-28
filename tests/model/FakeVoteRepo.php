<?php

namespace Schedule\Model;

class FakeVoteRepo extends VoteRepo
{
    private $options = [];

    private $votes = [];

    public function __construct($options = [])
    {
        $this->options = $options;
    }

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
        return $this->options["save"] ?? true;
    }
}
