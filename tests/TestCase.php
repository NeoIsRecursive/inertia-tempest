<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests;

use NeoIsRecursive\Inertia\Support\Header;
use Tempest\Framework\Testing\IntegrationTest;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;

abstract class TestCase extends IntegrationTest
{
    protected string $root = __DIR__ . '/../';

    public function createInertiaRequest(Method $method, string $uri): Request
    {
        $request = new GenericRequest(
            method: $method,
            uri: $uri,
            headers: [
                Header::INERTIA => true,
            ],
        );
        $this->container->singleton(Request::class, fn() => $request);
        return $request;
    }
}
