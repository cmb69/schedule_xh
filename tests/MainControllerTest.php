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
use PHPUnit\Framework\TestCase;
use Schedule\Infra\FakeRequest;
use Schedule\Infra\FakeVoteRepo;
use Schedule\Infra\View;
use Schedule\Value\Vote;

final class MainControllerTest extends TestCase
{
    public function testInvalidNameFails(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest, "christ!mas");
        Approvals::verifyHtml($response->output());
    }

    public function testNoOptionsFails(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest, "christmas");
        Approvals::verifyHtml($response->output());
    }

    public function testRender(): void
    {
        $voteRepo = new FakeVoteRepo;
        $voteRepo->save("color", new Vote("cmb", ["red"]));
        $voteRepo->save("color", new Vote("other", ["blue"]));
        $sut = $this->sut(["voteRepo" => $voteRepo]);
        $response = $sut(new FakeRequest, "color", "red", "green", "blue");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersOptionsWithSpecialChars(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest(["user" => "cmb"]), "special", "1 &lt; 2", "1 &gt; 2", "1 &amp; 2");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersWhatUserHasVoted(): void
    {
        $voteRepo = new FakeVoteRepo;
        $voteRepo->save("color", new Vote("cmb", ["red", "blue"]));
        $sut = $this->sut(["voteRepo" => $voteRepo]);
        $response = $sut(new FakeRequest(["user" => "cmb"]), "color", "red", "green", "blue");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersTotalsIfConfigured(): void
    {
        $voteRepo = new FakeVoteRepo;
        $voteRepo->save("color", new Vote("cmb", ["red"]));
        $voteRepo->save("color", new Vote("other", ["blue"]));
        $sut = $this->sut(["voteRepo" => $voteRepo]);
        $response = $sut(new FakeRequest, "color", true, "red", "green", "blue");
        Approvals::verifyHtml($response->output());
    }

    public function testSubmissionSuccess(): void
    {
        $voteRepo = new FakeVoteRepo;
        $voteRepo->save("color", new Vote("cmb", ["red"]));
        $voteRepo->save("color", new Vote("other", ["blue"]));
        $sut = $this->sut(["voteRepo" => $voteRepo]);
        $request = new FakeRequest(["user" => "cmb", "post" => [
            "schedule_date_color" => ["blue", "green"],
            "schedule_submit_color" => "vote",
        ]]);
        $response = $sut($request, "color", "red", "green", "blue");
        $this->assertEquals(
            ["cmb" => new Vote("cmb", ["blue", "green"]), "other" => new Vote("other", ["blue"])],
            $voteRepo->findAll("color", null)
        );
        $this->assertEquals("http://example.com/?Schedule", $response->location());
    }

    public function testCanSubmitNoChoices(): void
    {
        $voteRepo = new FakeVoteRepo;
        $voteRepo->save("color", new Vote("cmb", ["red"]));
        $sut = $this->sut(["voteRepo" => $voteRepo]);
        $request = new FakeRequest(["user" => "cmb", "post" => [
            "schedule_submit_color" => "vote",
        ]]);
        $response = $sut($request, "color", "red", "green", "blue");
        $this->assertEquals(["cmb" => new Vote("cmb", [])], $voteRepo->findAll("color"));
        $this->assertEquals("http://example.com/?Schedule", $response->location());
    }

    public function testSubmissionFailure(): void
    {
        $voteRepo = new FakeVoteRepo;
        $voteRepo->save("color", new Vote("cmb", ["red"]));
        $voteRepo->save("color", new Vote("other", ["blue"]));
        $sut = $this->sut(["voteRepo" => $voteRepo]);
        $request = new FakeRequest(["user" => "cmb", "post" => [
            "schedule_date_color" => ["yellow", "green"],
            "schedule_submit_color" => "vote",
        ]]);
        $sut($request, "color", "red", "green", "blue");
        $this->assertEquals(
            ["cmb" => new Vote("cmb", ["red"]), "other" => new Vote("other", ["blue"])],
            $voteRepo->findAll("color")
        );
    }

    public function testPostFailureIfNotLoggedIn(): void
    {
        $voteRepo = new FakeVoteRepo;
        $sut = $this->sut(["voteRepo" => $voteRepo]);
        $request = new FakeRequest(["post" => [
            "schedule_date_color" => ["blue", "green"],
            "schedule_submit_color" => "vote",
        ]]);
        $response = $sut($request, "color", "red", "green", "blue");
        Approvals::verifyHtml($response->output());
        $this->assertEmpty($voteRepo->findAll("color"));
    }

    public function testPostFailureIfReadonly(): void
    {
        $voteRepo = new FakeVoteRepo;
        $sut = $this->sut(["voteRepo" => $voteRepo]);
        $request = new FakeRequest(["user" => "cmb", "post" => [
            "schedule_date_color" => ["blue", "green"],
            "schedule_submit_color" => "vote",
        ]]);
        $response = $sut($request, "color", false, true, "red", "green", "blue");
        Approvals::verifyHtml($response->output());
        $this->assertEmpty($voteRepo->findAll("color"));
    }

    public function testPostFailureIfUnkownOptionsAreSupplied(): void
    {
        $voteRepo = new FakeVoteRepo;
        $sut = $this->sut(["voteRepo" => $voteRepo]);
        $request = new FakeRequest(["user" => "cmb", "post" => [
            "schedule_date_color" => ["yellow"],
            "schedule_submit_color" => "vote",
        ]]);
        $response = $sut($request, "color", "red", "green", "blue");
        Approvals::verifyHtml($response->output());
        $this->assertEmpty($voteRepo->findAll("color"));
    }

    public function testFailureToSaveVoteIsReported(): void
    {
        $voteRepo = new FakeVoteRepo(["save" => false]);
        $sut = $this->sut(["voteRepo" => $voteRepo]);
        $request = new FakeRequest(["user" => "cmb", "post" => [
            "schedule_date_color" => ["blue", "green"],
            "schedule_submit_color" => "vote",
        ]]);
        $response = $sut($request, "color", "red", "green", "blue");
        Approvals::verifyHtml($response->output());
    }

    private function sut($options = [])
    {
        return new MainController(
            $this->conf(),
            $options["voteRepo"] ?? new FakeVoteRepo,
            $this->view()
        );
    }

    private function conf()
    {
        return XH_includeVar("./config/config.php", "plugin_cf")['schedule'];
    }

    private function view()
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")['schedule']);
    }
}
