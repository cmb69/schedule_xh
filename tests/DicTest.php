<?php

namespace Schedule;

use PHPUnit\Framework\TestCase;

class DicTest extends TestCase
{
    public function setUp(): void
    {
        global $cf, $plugin_cf, $plugin_tx, $pth;

        $cf = ["language" => ["default" => ""]];
        $plugin_cf = ["schedule" => []];
        $plugin_tx = ["schedule" => []];
        $pth = ["folder" => ["content" => "", "plugins" => ""]];
    }

    public function testMakesMainController(): void
    {
        $this->assertInstanceOf(MainController::class, Dic::makeMainController());
    }

    public function testMakesCallBuilder(): void
    {
        $this->assertInstanceOf(CallBuilder::class, Dic::callBuilder());
    }

    public function testMakesInfoController(): void
    {
        $this->assertInstanceOf(InfoController::class, Dic::makeInfoController());
    }
}
