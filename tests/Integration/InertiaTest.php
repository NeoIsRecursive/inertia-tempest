<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Inertia;
use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Support\Header;
use NeoIsRecursive\Inertia\Tests\Fixtures\TestController;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Status;

use function Tempest\get;
use function Tempest\Router\uri;

final class InertiaTest extends TestCase
{
    private function createFactory(): Inertia
    {
        return $this->container->get(Inertia::class);
    }

    public function test_location_response_for_inertia_requests(): void
    {
        $this->container->singleton(Request::class, fn() => $this->createInertiaRequest(Method::GET, uri: '/'));

        $factory = $this->createFactory();

        $response = $factory->location(url: 'https://inertiajs.com');

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(
            expected: Status::CONFLICT,
            actual: $response->status,
        );
        static::assertSame(
            expected: 'https://inertiajs.com',
            actual: $response->getHeader(Header::LOCATION)->values[0],
        );
    }

    public function test_location_response_for_non_inertia_requests(): void
    {
        $factory = $this->createFactory();

        $response = $factory->location(url: 'https://inertiajs.com');

        static::assertInstanceOf(Redirect::class, $response);
        static::assertSame(
            expected: Status::FOUND,
            actual: $response->status,
        );
        static::assertSame(
            expected: 'https://inertiajs.com',
            actual: $response->getHeader(name: 'Location')->values[0],
        );
    }

    public function test_location_response_for_inertia_requests_using_redirect_response(): void
    {
        $this->container->singleton(Request::class, fn() => $this->createInertiaRequest(Method::GET, uri: '/'));
        $factory = $this->createFactory();

        $redirect = new Redirect(to: 'https://inertiajs.com');
        $response = $factory->location($redirect);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(
            expected: Status::CONFLICT,
            actual: $response->status,
        );
        static::assertSame(
            expected: 'https://inertiajs.com',
            actual: $response->getHeader(Header::LOCATION)->values[0],
        );
    }

    public function test_location_response_for_non_inertia_requests_using_redirect_response(): void
    {
        $redirect = new Redirect(to: 'https://inertiajs.com');
        $response = $this->createFactory()->location($redirect);

        static::assertInstanceOf(Redirect::class, $response);
        static::assertSame(
            expected: Status::FOUND,
            actual: $response->status,
        );
        static::assertSame(
            expected: 'https://inertiajs.com',
            actual: $response->getHeader(name: 'Location')->values[0],
        );
    }

    public function test_location_redirects_are_not_modified(): void
    {
        $response = $this->createFactory()->location(url: '/foo');

        static::assertInstanceOf(Redirect::class, $response);
        static::assertSame(
            expected: Status::FOUND,
            actual: $response->status,
        );
        static::assertSame(
            expected: '/foo',
            actual: $response->getHeader(name: 'Location')->values[0],
        );
    }

    public function test_location_response_for_non_inertia_requests_using_redirect_response_with_existing_session_and_request_properties(): void
    {
        $redirect = new Redirect(to: 'https://inertiajs.com');
        $redirect->addSession(
            name: 'foo',
            value: 'bar',
        );
        $response = $this->createFactory()->location($redirect);

        static::assertInstanceOf(Redirect::class, $response);
        static::assertSame(
            expected: Status::FOUND,
            actual: $response->status,
        );
        static::assertSame(
            expected: 'https://inertiajs.com',
            actual: $response->getHeader(name: 'Location')->values[0],
        );

        static::assertSame(
            expected: $response,
            actual: $redirect,
        );
    }

    public function test_returns_conflict_response_for_inertia_requests_with_different_version(): void
    {
        $response = $this->http->get(uri([TestController::class, 'testCanSharePropsFromAnyWhere']), headers: [
            Header::INERTIA => 'true',
            Header::VERSION => 'invalid-version',
        ]);

        $response->assertStatus(Status::CONFLICT);
    }

    public function test_returns_response_if_it_isnt_an_inertia_request(): void
    {
        $response = $this->http->get(uri([TestController::class, 'nonInertiaPage']));

        $response->assertOk();
        $response->assertHeaderContains(
            name: 'Vary',
            value: Header::INERTIA,
        );
        static::assertSame(
            expected: [
                'message' => 'This is a non-Inertia page.',
            ],
            actual: $response->body,
        );
    }

    public function test_shared_data_can_be_shared_from_anywhere(): void
    {
        $version = get(Inertia::class)->version;

        $response = $this->http->get(uri([TestController::class, 'testCanSharePropsFromAnyWhere']), headers: [
            Header::INERTIA => 'true',
            Header::VERSION => $version,
        ]);

        $response->assertOk();
        static::assertSame(
            expected: [
                'component' => 'User/Edit',
                'props' => [
                    'user' => null,
                    'errors' => [],
                    'foo' => 'bar',
                    'baz' => 'qux',
                ],
                'url' => uri([TestController::class, 'testCanSharePropsFromAnyWhere']),
                'version' => $version,
                'clearHistory' => false,
                'encryptHistory' => false,
            ],
            actual: $response->body->jsonSerialize(),
        );
    }

    public function test_can_flush_shared_data(): void
    {
        get(Inertia::class)->share(
            key: 'foo',
            value: 'bar',
        );

        static::assertArrayHasKey(
            key: 'foo',
            array: get(InertiaConfig::class)->sharedProps,
            message: 'bar',
        );
        get(Inertia::class)->flushShared();

        static::assertSame(
            expected: [],
            actual: get(InertiaConfig::class)->sharedProps,
        );
    }

    public function test_mergeable_props_can_be_merged(): void
    {
        $version = get(Inertia::class)->version;

        $response = $this->http->get(uri([TestController::class, 'testCanMergeProps']), headers: [
            Header::INERTIA => 'true',
            Header::VERSION => $version,
            Header::PARTIAL_COMPONENT => 'test',
        ]);

        $response->assertOk();
        static::assertSame(
            expected: [
                'component' => 'test',
                'props' => [
                    'user' => null,
                    'errors' => [],
                    'foo' => 'bar',
                    'baz' => 'qux',
                ],
                'url' => uri([TestController::class, 'testCanMergeProps']),
                'version' => $version,
                'clearHistory' => false,
                'encryptHistory' => false,
                'mergeProps' => [
                    'foo',
                    'baz',
                ],
            ],
            actual: $response->body->jsonSerialize(),
        );
    }

    public function test_array_helper_props_can_be_resolved(): void
    {
        $version = get(Inertia::class)->version;

        $response = $this->http->get(uri([TestController::class, 'testAllSortsOfProps']), headers: [
            Header::INERTIA => 'true',
            Header::VERSION => $version,
            Header::PARTIAL_COMPONENT => 'User/Edit',
            Header::PARTIAL_ONLY => 'optional,defer',
        ]);

        $response->assertOk();
        static::assertSame(
            expected: [
                'component' => 'User/Edit',
                'props' => [
                    'user' => null,
                    'errors' => [],
                    'shared' => [1, 2, 3],
                    'always' => ['always-1', 'always-2'],
                    'optional' => ['optional-1', 'optional-2'],
                    'defer' => ['defer-1', 'defer-2'],
                ],
                'url' => uri([TestController::class, 'testAllSortsOfProps']),
                'version' => $version,
                'clearHistory' => false,
                'encryptHistory' => false,
            ],
            actual: $response->body->jsonSerialize(),
        );
    }
}
