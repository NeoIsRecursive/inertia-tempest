<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests;

use NeoIsRecursive\Inertia\Support\Header;
use Tempest\Core\Application;
use Tempest\Core\DiscoveryLocation;
use Tempest\Framework\Testing\IntegrationTest;
use Tempest\Http\GenericRequest;
use Tempest\Http\HttpApplication;
use Tempest\Http\Method;
use Tempest\Http\Request;

abstract class TestCase extends IntegrationTest
{
    protected string $root = __DIR__ . '/../';

    public function setUp(): void
    {
        $this->discoveryLocations[] = new DiscoveryLocation(
            namespace: 'NeoIsRecursive\\Inertia\\Tests\\Fixtures\\',
            path: __DIR__ . '/Fixtures',
        );


        parent::setUp();

        $this->actAsHttpApplication();
    }

    protected function actAsHttpApplication(): HttpApplication
    {
        $application = new HttpApplication(
            $this->container,
        );

        $this->container->singleton(Application::class, fn() => $application);

        return $application;
    }

    public function createInertiaRequest(Method $method, string $uri, array $headers = []): Request
    {
        $request = new GenericRequest(
            method: $method,
            uri: $uri,
            headers: [
                Header::INERTIA => true,
                ...$headers,
            ],
        );
        $this->container->singleton(Request::class, fn() => $request);
        return $request;
    }
}
