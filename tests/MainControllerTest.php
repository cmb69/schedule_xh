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

    /** @var VotingService&MockObject */
    private $votingService;

    public function setUp(): void
    {
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        assert(is_array($plugin_cf));
        $this->conf = $plugin_cf['schedule'];
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        assert(is_array($plugin_tx));
        $this->lang = $plugin_tx['schedule'];
        $this->votingService = $this->createMock(VotingService::class);
    }

    public function testInvalidNameFails(): void
    {
        $sut = new MainController([], "", $this->votingService, "./", $this->lang);

        $response = $sut->execute("christ!mas");

        Approvals::verifyHtml($response);
    }

    public function testNoOptionsFails(): void
    {
        $sut = new MainController($this->conf, "", $this->votingService, "./", $this->lang);

        $response = $sut->execute("christmas");

        Approvals::verifyHtml($response);
    }

    public function testRender(): void
    {
        $sut = new MainController($this->conf, "", $this->votingService, "./", $this->lang);
        $this->votingService->method("findAll")->willReturn([
            "cmb" => ["red"],
            "other" => ["blue"],
        ]);

        $response = $sut->execute("color", "red", "green", "blue");

        Approvals::verifyHtml($response);
    }

    public function testRendersTotalsIfConfigured(): void
    {
        $sut = new MainController($this->conf, "", $this->votingService, "./", $this->lang);
        $this->votingService->method("findAll")->willReturn([
            "cmb" => ["red"],
            "other" => ["blue"],
        ]);

        $response = $sut->execute("color", true, "red", "green", "blue");

        Approvals::verifyHtml($response);
    }

    public function testSubmissionSuccess(): void
    {
        $_SESSION['username'] = "cmb";
        $_POST = [
            "schedule_date_color" => ["blue", "green"],
            "schedule_submit_color" => "Save",
        ];
        $sut = new MainController($this->conf, "", $this->votingService, "./", $this->lang);
        $this->votingService->method("findAll")->willReturn([
            "cmb" => ["red"],
            "other" => ["blue"],
        ]);
        $this->votingService->expects($this->once())->method("vote")->with(
            "color",
            "cmb",
            ["blue", "green"]
        );
        $sut->execute("color", "red", "green", "blue");
    }

    public function testSubmissionFailure(): void
    {
        $_SESSION['username'] = "cmb";
        $_POST = [
            "schedule_date_color" => ["yellow", "green"],
            "schedule_submit_color" => "Save",
        ];
        $sut = new MainController($this->conf, "", $this->votingService, "./", $this->lang);
        $this->votingService->method("findAll")->willReturn([
            "cmb" => ["red"],
            "other" => ["blue"],
        ]);
        $this->votingService->expects($this->never())->method("vote");
        $sut->execute("color", "red", "green", "blue");
    }
}
