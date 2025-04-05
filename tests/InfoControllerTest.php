<?php

namespace Schedule;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore;
use Plib\FakeSystemChecker;
use Plib\View;

use function XH_includeVar;

final class InfoControllerTest extends TestCase
{
    public function testRendersPluginInfo(): void
    {
        $store = $this->createStub(DocumentStore::class);
        $store->method("folder")->willReturn("./content/schedule/");
        $sut = new InfoController("./plugins/schedule/", $store, $this->view(), new FakeSystemChecker());
        $response = $sut();
        $this->assertEquals("Schedule 2.1", $response->title());
        Approvals::verifyHtml($response->output());
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["schedule"]);
    }
}
