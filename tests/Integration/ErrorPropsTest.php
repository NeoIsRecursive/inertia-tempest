<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\PageData;
use NeoIsRecursive\Inertia\Tests\Fixtures\TestController;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Http\Session\Session;

use function Tempest\Router\uri;

final class ErrorPropsTest extends TestCase
{
    public function test_error_props(): void
    {
        $this->http->post(
            uri([TestController::class, 'testValidationErrors']),
            body: [
                'firstName' => '',
                'lastName' => '',
                'email' => 'not-an-email',
                'age' => -2,
            ],
            headers: [
                'X-Inertia' => 'true',
                'X-Version' => '1.0',
            ],
        );

        $response = $this->http->get(uri([TestController::class, 'index']), headers: [
            'X-Inertia' => 'true',
            'X-Version' => '1.0',
        ]);

        /** @var PageData */
        $body = $response->body;

        $response->assertHasSession(Session::VALIDATION_ERRORS);

        $this->assertArrayHasKey('errors', $body->props);
        $this->assertArrayHasKey('firstName', $body->props['errors']);
        $this->assertCount(1, $body->props['errors']['firstName']);
        $this->assertCount(2, $body->props['errors']['age']);
    }
}
