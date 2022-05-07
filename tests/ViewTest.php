<?php

/**
 * Copyright 2022 Christoph M. Becker
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

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

final class ViewTest extends TestCase
{
    public function testRender(): void
    {
        $root = vfsStream::setup("home");
        vfsStream::newFile("foo.php")->at($root)->setContent(
            "<p><?=\$bar?></p>"
        );
        $sut = new View($root->url(), []);
        $this->assertXmlStringEqualsXmlString("<p>baz</p>", $sut->render("foo", ["bar" => "baz"]));
    }

    public function testText(): void
    {
        $sut = new View("", ["foo" => "bar"]);
        $this->assertEquals("bar", $sut->text("foo"));
    }

    public function testWarn(): void
    {
        $sut = new View("", ["foo" => "bar"]);
        $this->assertEquals("<p class=\"cmsimplecore_warning\">bar</p>", $sut->warn("foo"));
    }
}
