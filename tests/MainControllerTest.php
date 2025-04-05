<?php

namespace Schedule;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\DocumentStore;
use Plib\FakeRequest;
use Plib\View;
use Schedule\Model\Voting;

final class MainControllerTest extends TestCase
{
    public function testInvalidNameFails(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest(), "christ!mas");
        Approvals::verifyHtml($response->output());
    }

    public function testNoOptionsFails(): void
    {
        $sut = $this->sut();
        $response = $sut(new FakeRequest(), "christmas");
        Approvals::verifyHtml($response->output());
    }

    public function testRender(): void
    {
        $voting = Voting::fromString("cmb\tred\nother\tblue");
        $store = $this->createStub(DocumentStore::class);
        $store->method("retrieve")->willReturn($voting);
        $sut = $this->sut(["store" => $store]);
        $response = $sut(new FakeRequest(["url" => "http://example.com/?Schedule"]), "color", "red", "green", "blue");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersOptionsWithSpecialChars(): void
    {
        $sut = $this->sut();
        $request = new FakeRequest([
            "url" => "http://example.com/?Schedule",
            "username" => "cmb",
        ]);
        $response = $sut($request, "special", "1 &lt; 2", "1 &gt; 2", "1 &amp; 2");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersWhatUserHasVoted(): void
    {
        $store = $this->createStub(DocumentStore::class);
        $store->method("retrieve")->willReturn(Voting::fromString("cmb\tred\tblue"));
        $sut = $this->sut(["store" => $store]);
        $response = $sut(new FakeRequest(["username" => "cmb"]), "color", "red", "green", "blue");
        Approvals::verifyHtml($response->output());
    }

    public function testRendersTotalsIfConfigured(): void
    {
        $store = $this->createStub(DocumentStore::class);
        $store->method("retrieve")->willReturn(Voting::fromString("cmb\tred\nother\tblue"));
        $sut = $this->sut(["store" => $store]);
        $response = $sut(new FakeRequest(), "color", true, "red", "green", "blue");
        Approvals::verifyHtml($response->output());
    }

    public function testSubmissionSuccess(): void
    {
        $voting = Voting::fromString("cmb\tred\n\nother\tblue");
        $store = $this->createStub(DocumentStore::class);
        $store->method("update")->willReturn($voting);
        $store->expects($this->once())->method("commit")->willReturn(true);
        $sut = $this->sut(["store" => $store]);
        $request = new FakeRequest([
            "url" => "http://example.com/?Schedule",
            "username" => "cmb",
            "post" => [
                "schedule_date_color" => ["blue", "green"],
                "schedule_submit_color" => "vote",
            ],
        ]);
        $response = $sut($request, "color", "red", "green", "blue");
        $this->assertSame("cmb\tblue\tgreen\nother\tblue", $voting->toString());
        $this->assertEquals("http://example.com/?Schedule", $response->location());
    }

    public function testCanSubmitNoChoices(): void
    {
        $voting = Voting::fromString("cmb\tred");
        $store = $this->createStub(DocumentStore::class);
        $store->method("update")->willReturn($voting);
        $store->expects($this->once())->method("commit")->willReturn(true);
        $sut = $this->sut(["store" => $store]);
        $request = new FakeRequest([
            "url" => "http://example.com/?Schedule",
            "username" => "cmb",
            "post" => [
                "schedule_submit_color" => "vote",
                ]
            ]);
        $response = $sut($request, "color", "red", "green", "blue");
        $this->assertSame("cmb", $voting->toString());
        $this->assertEquals("http://example.com/?Schedule", $response->location());
    }

    public function testSubmissionFailure(): void
    {
        $sut = $this->sut();
        $request = new FakeRequest(["username" => "cmb", "post" => [
            "schedule_date_color" => ["yellow", "green"],
            "schedule_submit_color" => "vote",
        ]]);
        $response = $sut($request, "color", "red", "green", "blue");
        $this->assertStringContainsString("Something went wrong with voting! Please try again.", $response->output());
    }

    public function testPostFailureIfNotLoggedIn(): void
    {
        $sut = $this->sut();
        $request = new FakeRequest(["post" => [
            "schedule_date_color" => ["blue", "green"],
            "schedule_submit_color" => "vote",
        ]]);
        $response = $sut($request, "color", "red", "green", "blue");
        Approvals::verifyHtml($response->output());
    }

    public function testPostFailureIfReadonly(): void
    {
        $sut = $this->sut();
        $request = new FakeRequest(["username" => "cmb", "post" => [
            "schedule_date_color" => ["blue", "green"],
            "schedule_submit_color" => "vote",
        ]]);
        $response = $sut($request, "color", false, true, "red", "green", "blue");
        Approvals::verifyHtml($response->output());
    }

    public function testVotingIsCsrfProtected(): void
    {
        $voting = Voting::fromString("");
        $store = $this->createStub(DocumentStore::class);
        $store->method("retrieve")->willReturn($voting);
        $csrfProtector = $this->createStub(CsrfProtector::class);
        $csrfProtector->method("check")->willReturn(false);
        $sut = $this->sut(["store" => $store, "csrf" => $csrfProtector]);
        $request = new FakeRequest(["username" => "cmb", "post" => [
            "schedule_date_color" => ["blue", "green"],
            "schedule_submit_color" => "vote",
        ]]);
        $response = $sut($request, "color", "red", "green", "blue");
        $this->assertStringContainsString("Something went wrong with voting! Please try again.", $response->output());
    }

    public function testPostFailureIfUnkownOptionsAreSupplied(): void
    {
        $sut = $this->sut();
        $request = new FakeRequest([
            "url" => "http://example.com/?Schedule",
            "username" => "cmb",
            "post" => [
                "schedule_date_color" => ["yellow"],
                "schedule_submit_color" => "vote",
            ],
        ]);
        $response = $sut($request, "color", "red", "green", "blue");
        Approvals::verifyHtml($response->output());
    }

    public function testFailureToSaveVoteIsReported(): void
    {
        $store = $this->createStub(DocumentStore::class);
        $voting = Voting::fromString("");
        $store->method("retrieve")->willReturn($voting);
        $store->method("update")->willReturn($voting);
        $store->expects($this->once())->method("commit")->willReturn(false);
        $sut = $this->sut(["store" => $store]);
        $request = new FakeRequest([
            "url" => "http://example.com/?Schedule",
            "username" => "cmb",
            "post" => [
                "schedule_date_color" => ["blue", "green"],
                "schedule_submit_color" => "vote",
            ],
        ]);
        $response = $sut($request, "color", "red", "green", "blue");
        Approvals::verifyHtml($response->output());
    }

    private function sut($options = [])
    {
        $store = $this->createStub(DocumentStore::class);
        $store->method("retrieve")->willReturn(Voting::fromString(""));
        $csrfProtector = $this->createStub(CsrfProtector::class);
        $csrfProtector->method("token")->willReturn("123456789");
        $csrfProtector->method("check")->willReturn(true);
        return new MainController(
            $this->conf(),
            $options["store"] ?? $store,
            $options["csrf"] ?? $csrfProtector,
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
