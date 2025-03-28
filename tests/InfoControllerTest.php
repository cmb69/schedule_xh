<?php

namespace Schedule;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use Plib\FakeSystemChecker;
use Plib\View;
use Schedule\Model\FakeVoteRepo;

use function XH_includeVar;

final class InfoControllerTest extends TestCase
{
    public function testRendersPluginInfo(): void
    {
        $sut = new InfoController("./plugins/schedule/", new FakeVoteRepo(), $this->view(), new FakeSystemChecker());
        $response = $sut();
        $this->assertEquals("Schedule 2.1-dev", $response->title());
        Approvals::verifyHtml($response->output());
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["schedule"]);
    }
}
