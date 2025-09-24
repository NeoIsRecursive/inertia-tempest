<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests;

use NeoIsRecursive\Inertia\Support\Header;
use Override;
use Tempest\Core\Application;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Framework\Testing\IntegrationTest;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Router\HttpApplication;
use Tempest\View\GenericView;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

abstract class TestCase extends IntegrationTest
{
    protected string $root = __DIR__ . '/../';

    #[Override]
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
        $application = new HttpApplication($this->container);

        $this->container->singleton(Application::class, fn() => $application);

        return $application;
    }

    public function createInertiaRequest(Method $method, string $uri, array $headers = []): Request
    {
        $request = new GenericRequest(
            method: $method,
            uri: $uri,
            headers: [
                Header::INERTIA => 'true',
                ...$headers,
            ],
        );
        $this->container->singleton(Request::class, fn() => $request);
        return $request;
    }

    public function assertArraySubsetValues(array $subset, array $array): void
    {
        foreach ($subset as $key => $value) {
            $this->assertArrayHasKey($key, $array);
            $this->assertSame($value, $array[$key]);
        }
    }

    public static function assertSnippetsMatch(string $expected, string $actual): void
    {
        $expected = str_replace([PHP_EOL, ' '], replace: '', subject: $expected);
        $actual = str_replace([PHP_EOL, ' '], replace: '', subject: $actual);

        static::assertSame($expected, $actual);
    }

    protected function render(string|View $view, mixed ...$params): string
    {
        if (is_string($view)) {
            $view = new GenericView($view);
        }

        $view->data(...$params);

        return $this->container->get(ViewRenderer::class)->render($view);
    }
}
