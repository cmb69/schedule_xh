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

use function XH_includeVar;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use Schedule\Infra\FakeRequest;
use Schedule\Infra\FakeSystemChecker;
use Schedule\Infra\View;
use Schedule\Infra\VoteRepo;

final class InfoControllerTest extends TestCase
{
    public function testRendersPluginInfo(): void
    {
        $sut = new InfoController($this->voteRepo(), $this->view(), new FakeSystemChecker);
        $request = new FakeRequest(["pth" => ["folder" => ["plugins" => "./plugins/"]]]);
        $response = $sut->execute($request);
        Approvals::verifyHtml($response->output());
    }

    private function voteRepo()
    {
        $voteRepo = $this->createStub(VoteRepo::class);
        $voteRepo->method('dataFolder')->willReturn("./content/schedule/");
        return $voteRepo;
    }

    private function view()
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["schedule"]);
    }
}
