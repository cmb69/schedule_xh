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

namespace Schedule;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MainControllerTest extends TestCase
{
    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $lang;

    /** @var VotingService */
    private $votingService;

    public function setUp(): void
    {
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        assert(is_array($plugin_cf));
        $this->conf = $plugin_cf['schedule'];
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        assert(is_array($plugin_tx));
        $this->lang = $plugin_tx['schedule'];
        $this->votingService = new class extends VotingService
        {
            /** @var array<array<string,array<string>>> */
            private $votes;
            public function __construct()
            {
            }
            public function dataFolder(): string
            {
                return "no_folder/";
            }
            public function findAll(string $name, ?string $user, bool $sorted): array
            {
                return $this->votes[$name];
            }
            public function vote(string $name, string $user, array $options): bool
            {
                $this->votes[$name][$user] = $options;
                return true;
            }
        };
    }

    public function testInvalidNameFails(): void
    {
        $sut = new MainController([], "", $this->votingService, "./", $this->lang, null);

        $response = $sut->execute("christ!mas");

        Approvals::verifyHtml($response);
    }

    public function testNoOptionsFails(): void
    {
        $sut = new MainController($this->conf, "", $this->votingService, "./", $this->lang, null);

        $response = $sut->execute("christmas");

        Approvals::verifyHtml($response);
    }

    public function testRender(): void
    {
        $this->votingService->vote("color", "cmb", ["red"]);
        $this->votingService->vote("color", "other", ["blue"]);
        $sut = new MainController($this->conf, "", $this->votingService, "./", $this->lang, null);

        $response = $sut->execute("color", "red", "green", "blue");

        Approvals::verifyHtml($response);
    }

    public function testRendersTotalsIfConfigured(): void
    {
        $this->votingService->vote("color", "cmb", ["red"]);
        $this->votingService->vote("color", "other", ["blue"]);
        $sut = new MainController($this->conf, "", $this->votingService, "./", $this->lang, null);

        $response = $sut->execute("color", true, "red", "green", "blue");

        Approvals::verifyHtml($response);
    }

    public function testSubmissionSuccess(): void
    {
        $_POST = [
            "schedule_date_color" => ["blue", "green"],
            "schedule_submit_color" => "Save",
        ];
        $this->votingService->vote("color", "cmb", ["red"]);
        $this->votingService->vote("color", "other", ["blue"]);
        $sut = new MainController($this->conf, "", $this->votingService, "./", $this->lang, "cmb");
        $sut->execute("color", "red", "green", "blue");

        $this->assertEquals(
            ["cmb" => ["blue", "green"], "other" => ["blue"]],
            $this->votingService->findAll("color", null, true)
        );
    }

    public function testSubmissionFailure(): void
    {
        $_POST = [
            "schedule_date_color" => ["yellow", "green"],
            "schedule_submit_color" => "Save",
        ];
        $this->votingService->vote("color", "cmb", ["red"]);
        $this->votingService->vote("color", "other", ["blue"]);
        $sut = new MainController($this->conf, "", $this->votingService, "./", $this->lang, "cmb");

        $sut->execute("color", "red", "green", "blue");

        $this->assertEquals(
            ["cmb" => ["red"], "other" => ["blue"]],
            $this->votingService->findAll("color", null, true)
        );
    }
}
