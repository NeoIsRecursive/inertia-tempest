<?php

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Inertia;
use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Support\Header;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;

use function Tempest\get;

class InertiaTest extends TestCase
{
    private function createFactory(): Inertia
    {
        return new Inertia($this->container, $this->container->get(InertiaConfig::class));
    }

    public function test_location_response_for_inertia_requests(): void
    {
        $this->container->singleton(Request::class, fn() =>  $this->createInertiaRequest(Method::GET, '/'));

        $factory = $this->createFactory();

        $response = $factory->location('https://inertiajs.com');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Status::CONFLICT, $response->getStatus());
        $this->assertEquals('https://inertiajs.com', $response->getHeader(Header::LOCATION)->values[0]);
    }

    public function test_location_response_for_non_inertia_requests(): void
    {
        $factory = $this->createFactory();

        $response = $factory->location('https://inertiajs.com');

        $this->assertInstanceOf(Redirect::class, $response);
        $this->assertEquals(Status::FOUND, $response->getStatus());
        $this->assertEquals('https://inertiajs.com', $response->getHeader('Location')->values[0]);
    }

    public function test_location_response_for_inertia_requests_using_redirect_response(): void
    {
        $this->container->singleton(Request::class, fn() =>  $this->createInertiaRequest(Method::GET, '/'));
        $factory = $this->createFactory();

        $redirect = new Redirect('https://inertiajs.com');
        $response = $factory->location($redirect);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Status::CONFLICT, $response->getStatus());
        $this->assertEquals('https://inertiajs.com', $response->getHeader(Header::LOCATION)->values[0]);
    }

    public function test_location_response_for_non_inertia_requests_using_redirect_response(): void
    {
        $redirect = new Redirect(to: 'https://inertiajs.com');
        $response = $this->createFactory()->location($redirect);

        $this->assertInstanceOf(Redirect::class, $response);
        $this->assertEquals(Status::FOUND, $response->getStatus());
        $this->assertEquals('https://inertiajs.com', $response->getHeader('Location')->values[0]);
    }

    public function test_location_redirects_are_not_modified(): void
    {
        $response = $this->createFactory()->location('/foo');

        $this->assertInstanceOf(Redirect::class, $response);
        $this->assertEquals(Status::FOUND, $response->getStatus());
        $this->assertEquals('/foo', $response->getHeader('Location')->values[0]);
    }

    public function test_location_response_for_non_inertia_requests_using_redirect_response_with_existing_session_and_request_properties(): void
    {
        $redirect = new Redirect('https://inertiajs.com');
        $redirect->addSession('foo', 'bar');
        $response = $this->createFactory()->location($redirect);

        $this->assertInstanceOf(Redirect::class, $response);
        $this->assertEquals(Status::FOUND, $response->getStatus());
        $this->assertEquals('https://inertiajs.com', $response->getHeader('Location')->values[0]);
        // $this->assertSame(get(Session::class), $response->());
        // $this->assertSame($request, $response->getRequest());
        $this->assertSame($response, $redirect);
    }

    // public function test_the_version_can_be_a_closure(): void
    // {
    //     Route::middleware([StartSession::class, ExampleMiddleware::class])->get('/', function () {
    //         $this->assertSame('', Inertia::getVersion());

    //         Inertia::version(function () {
    //             return md5('Inertia');
    //         });

    //         return Inertia::render('User/Edit');
    //     });

    //     $response = $this->withoutExceptionHandling()->get('/', [
    //         'X-Inertia' => 'true',
    //         'X-Inertia-Version' => 'b19a24ee5c287f42ee1d465dab77ab37',
    //     ]);

    //     $response->assertSuccessful();
    //     $response->assertJson(['component' => 'User/Edit']);
    // }

    // public function test_shared_data_can_be_shared_from_anywhere(): void
    // {
    //     Route::middleware([StartSession::class, ExampleMiddleware::class])->get('/', function () {
    //         Inertia::share('foo', 'bar');

    //         return Inertia::render('User/Edit');
    //     });

    //     $response = $this->withoutExceptionHandling()->get('/', ['X-Inertia' => 'true']);

    //     $response->assertSuccessful();
    //     $response->assertJson([
    //         'component' => 'User/Edit',
    //         'props' => [
    //             'foo' => 'bar',
    //         ],
    //     ]);
    // }

    // public function test_can_flush_shared_data(): void
    // {
    //     Inertia::share('foo', 'bar');
    //     $this->assertSame(['foo' => 'bar'], Inertia::getShared());
    //     Inertia::flushShared();
    //     $this->assertSame([], Inertia::getShared());
    // }

    // public function test_can_create_lazy_prop(): void
    // {
    //     $factory = new ResponseFactory();
    //     $lazyProp = $factory->lazy(function () {
    //         return 'A lazy value';
    //     });

    //     $this->assertInstanceOf(LazyProp::class, $lazyProp);
    // }

    // public function test_can_create_always_prop(): void
    // {
    //     $factory = new ResponseFactory();
    //     $alwaysProp = $factory->always(function () {
    //         return 'An always value';
    //     });

    //     $this->assertInstanceOf(AlwaysProp::class, $alwaysProp);
    // }

    // public function test_will_accept_arrayabe_props()
    // {
    //     Route::middleware([StartSession::class, ExampleMiddleware::class])->get('/', function () {
    //         Inertia::share('foo', 'bar');

    //         return Inertia::render('User/Edit', new class() implements Arrayable {
    //             public function toArray()
    //             {
    //                 return [
    //                     'foo' => 'bar',
    //                 ];
    //             }
    //         });
    //     });

    //     $response = $this->http->get('/', ['X-Inertia' => 'true']);
    //     $response->assertSuccessful();
    //     $response->assertJson([
    //         'component' => 'User/Edit',
    //         'props' => [
    //             'foo' => 'bar',
    //         ],
    //     ]);
    // }
}
