<?php

namespace Schedule;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use Plib\FakeRequest;
use Plib\View;

class CallBuilderTest extends TestCase
{
    public function testRendersCallBuilder(): void
    {
        $sut = new CallBuilder(
            "./plugins/schedule/",
            XH_includeVar("./config/config.php", "plugin_cf")["schedule"],
            new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["schedule"])
        );
        Approvals::verifyHtml($sut(new FakeRequest())->output());
    }
}
