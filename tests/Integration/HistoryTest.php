<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Inertia;
use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Support\Header;
use NeoIsRecursive\Inertia\Tests\Fixtures\TestController;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Http\Session\Session;

use function Tempest\get;
use function Tempest\uri;

class HistoryTest extends TestCase
{
    public function test_the_history_is_not_encrypted_or_cleared_by_default(): void
    {
        $version = get(Inertia::class)->version;

        $response = $this->http->get(uri([TestController::class, 'testCanSharePropsFromAnyWhere']), headers: [
            Header::INERTIA => 'true',
            Header::VERSION => $version,
        ]);

        $response->assertOk();
        static::assertArraySubsetValues([
            'component' => 'User/Edit',
            'encryptHistory' => false,
            'clearHistory' => false,
        ], $response->body);
    }

    public function test_the_history_can_be_encrypted(): void
    {
        $version = get(Inertia::class)->version;

        $response = $this->http->get(uri([TestController::class, 'testEncryptedHistory']), headers: [
            Header::INERTIA => 'true',
            Header::VERSION => $version,
        ]);

        $response->assertOk();
        $this->assertArraySubsetValues([
            'component' => 'User/Edit',
            'encryptHistory' => true,
        ], $response->body);
    }

    public function test_the_history_can_be_encrypted_via_middleware(): void
    {
        static::markTestIncomplete(message: 'This test is incomplete and needs to be fixed.');

        //     Route::middleware([StartSession::class, ExampleMiddleware::class, EncryptHistoryMiddleware::class])->get('/', function () {
        //         return Inertia::render('User/Edit');
        //     });
        //     $response = $this->withoutExceptionHandling()->get('/', [
        //         'X-Inertia' => 'true',
        //     ]);
        //     $response->assertSuccessful();
        //     $response->assertJson([
        //         'component' => 'User/Edit',
        //         'encryptHistory' => true,
        //     ]);
    }

    public function test_the_history_can_be_encrypted_globally(): void
    {
        static::markTestIncomplete(message: 'This test is incomplete and needs to be fixed.');

        // Route::middleware([StartSession::class, ExampleMiddleware::class])->get('/', function () {
        //     Config::set('inertia.history.encrypt', true);
        //     return Inertia::render('User/Edit');
        // });
        // $response = $this->withoutExceptionHandling()->get('/', [
        //     'X-Inertia' => 'true',
        // ]);
        // $response->assertSuccessful();
        // $response->assertJson([
        //     'component' => 'User/Edit',
        //     'encryptHistory' => true,
        // ]);
    }

    public function test_the_history_can_be_encrypted_globally_and_overridden(): void
    {
        $response = $this->http->get(uri([TestController::class, 'testEncryptedHistoryWithMiddleware']), headers: [
            Header::INERTIA => 'true',
        ]);

        $response->assertOk();
        $this->assertArraySubsetValues([
            'component' => 'User/Edit',
            'encryptHistory' => true,
        ], $response->body);
    }

    public function test_the_history_can_be_cleared(): void
    {
        $version = get(Inertia::class)->version;
        $response = $this->http->get(uri([TestController::class, 'testClearedHistory']), headers: [
            Header::INERTIA => 'true',
            Header::VERSION => $version,
        ]);

        $response->assertOk();
        $this->assertArraySubsetValues([
            'component' => 'User/Edit',
            'clearHistory' => true,
        ], $response->body);
    }

    public function test_the_history_can_be_cleared_when_redirecting(): void
    {
        $version = get(Inertia::class)->version;
        $response = $this->http->get(uri([TestController::class, 'testRedirectWithClearHistory']), headers: [
            Header::INERTIA => 'true',
            Header::VERSION => $version,
        ]);

        $response->assertRedirect(uri([TestController::class, 'testCanSharePropsFromAnyWhere']));

        static::assertTrue(get(Session::class)->get(key: 'inertia.clear_history'));

        // $response->assertContent('<div id="app" data-page="{&quot;component&quot;:&quot;User\/Edit&quot;,&quot;props&quot;:{&quot;errors&quot;:{}},&quot;url&quot;:&quot;\/users&quot;,&quot;version&quot;:&quot;&quot;,&quot;clearHistory&quot;:true,&quot;encryptHistory&quot;:false}"></div>');
    }
}
