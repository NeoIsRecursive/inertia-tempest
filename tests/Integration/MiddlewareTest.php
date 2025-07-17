<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Tests\Fixtures\TestController;
use NeoIsRecursive\Inertia\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Http\Status;

use function Tempest\uri;

final class MiddlewareTest extends TestCase
{
    public function test_middleware_is_registered(): void
    {
        $middleware = $this->container->get(\NeoIsRecursive\Inertia\Http\Middleware::class);

        static::assertInstanceOf(\NeoIsRecursive\Inertia\Http\Middleware::class, $middleware);
    }

    #[TestWith([
        'testPostWithRedirect',
        'post',
    ], name: 'post')]
    #[TestWith([
        'testPutWithRedirect',
        'put',
    ], name: 'put')]
    #[TestWith([
        'testPatchWithRedirect',
        'patch',
    ], name: 'patch')]
    public function test_middleware_converts_post_put_or_patch_302_to_303(string $action, string $method): void
    {
        $response = $this->http->{$method}(uri([TestController::class, $action]), headers: [
            'X-Inertia' => 'true',
            'X-Version' => '1.0',
        ]);

        $response->assertStatus(Status::SEE_OTHER);
    }
}
