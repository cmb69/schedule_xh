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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class MainControllerTest extends TestCase
{
    /** @var array<string,string> */
    private $conf;

    /** @var VotingService&MockObject */
    private $votingService;

    /** @var View&MockObject */
    private $view;

    public function setUp(): void
    {
        $this->conf = [
            "default_totals" => "",
            "default_readonly" => "",
            "default_multi" => "1",
            "sort_users" => "true",
        ];
        $this->votingService = $this->createMock(VotingService::class);
        $this->view = $this->createMock(View::class);
    }

    public function testInvalidNameFails(): void
    {
        $sut = new MainController([], "", $this->votingService, $this->view);
        $this->view->expects($this->once())->method("fail")->with($this->equalTo("err_invalid_name"));
        $sut->execute("christ!mas");
    }

    public function testNoOptionsFails(): void
    {
        $sut = new MainController($this->conf, "", $this->votingService, $this->view);
        $this->view->expects($this->once())->method("fail")->with($this->equalTo("err_no_option"));
        $sut->execute("christmas");
    }

    public function testRender(): void
    {
        $sut = new MainController($this->conf, "", $this->votingService, $this->view);
        $this->votingService->method("findAll")->willReturn([
            "cmb" => ["red"],
            "other" => ["blue"],
        ]);
        $this->view->expects($this->once())->method("render")->with("planner", [
            "showTotals" => false,
            "currentUser" => null,
            "url" => "",
            "options" => ["red", "green", "blue"],
            "iname" => "schedule_date_color",
            "sname" => "schedule_submit_color",
            "itype" => "checkbox",
            "columns" => 4,
            "counts" => ["red" => 1, "green" => 0, "blue" => 1],
            "users" => [
                "cmb" => [
                    "red" => "schedule_green",
                    "green" => "schedule_red",
                    "blue" => "schedule_red",
                ],
                "other" => [
                    "red" => "schedule_red",
                    "green" => "schedule_red",
                    "blue" => "schedule_green",
                ],
            ],
        ]);
        $sut->execute("color", "red", "green", "blue");
    }

    public function testSubmissionSuccess(): void
    {
        $_SESSION['username'] = "cmb";
        $_POST = [
            "schedule_date_color" => ["blue", "green"],
            "schedule_submit_color" => "Save",
        ];
        $sut = new MainController($this->conf, "", $this->votingService, $this->view);
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
        $sut = new MainController($this->conf, "", $this->votingService, $this->view);
        $this->votingService->method("findAll")->willReturn([
            "cmb" => ["red"],
            "other" => ["blue"],
        ]);
        $this->votingService->expects($this->never())->method("vote");
        $sut->execute("color", "red", "green", "blue");
    }
}
