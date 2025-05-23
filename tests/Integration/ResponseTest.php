<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Http\InertiaResponse;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\DeferProp;
use NeoIsRecursive\Inertia\Props\LazyProp;
use NeoIsRecursive\Inertia\Support\Header;
use NeoIsRecursive\Inertia\Tests\TestCase;
use NeoIsRecursive\Inertia\Views\InertiaBaseView;
use Tempest\Http\Method;
use Tempest\Router\GenericRequest;
use Tempest\Router\Response;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

use function Tempest\get;

final class ResponseTest extends TestCase
{
    public function test_server_response(): void
    {
        $request = new GenericRequest(Method::GET, '/user/123');

        $user = ['name' => 'Jonathan'];
        $response = new InertiaResponse(
            $request,
            'User/Edit',
            ['user' => $user],
            __DIR__ . '/../Fixtures/root.view.php',
            '123',
        );
        $view = $response->body;
        $page = $view->get('pageData');

        static::assertInstanceOf(Response::class, $response);
        static::assertInstanceOf(View::class, $view);

        static::assertSame('User/Edit', $page['component']);
        static::assertSame('Jonathan', $page['props']['user']['name']);
        static::assertSame('/user/123', $page['url']);
        static::assertSame('123', $page['version']);
        static::assertSame(
            '<main>     <div id="app" data-page="{&quot;component&quot;:&quot;User\/Edit&quot;,&quot;props&quot;:{&quot;user&quot;:{&quot;name&quot;:&quot;Jonathan&quot;}},&quot;url&quot;:&quot;\/user\/123&quot;,&quot;version&quot;:&quot;123&quot;}"></div></main>',
            get(ViewRenderer::class)->render($view),
        );
    }

    public function test_xhr_response(): void
    {
        $request = $this->createInertiaRequest(Method::GET, '/user/123');

        $user = ['name' => 'Jonathan'];
        $response = new InertiaResponse($request, 'User/Edit', ['user' => $user], 'app', '123');

        $page = $response->body;

        static::assertInstanceOf(Response::class, $response);

        static::assertSame('User/Edit', $page['component']);
        static::assertSame('Jonathan', $page['props']['user']['name']);
        static::assertSame('/user/123', $page['url']);
        static::assertSame('123', $page['version']);
    }

    // public function test_resource_response(): void
    // {
    //     $request = Request::create('/user/123', 'GET');
    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $resource = new FakeResource(['name' => 'Jonathan']);

    //     $response = new Response('User/Edit', ['user' => $resource], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $page = $response->getData();

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $this->assertSame('User/Edit', $page->component);
    //     $this->assertSame('Jonathan', $page->props->user->name);
    //     $this->assertSame('/user/123', $page->url);
    //     $this->assertSame('123', $page->version);
    // }

    public function test_lazy_resource_response(): void
    {
        $request = $this->createInertiaRequest(
            method: Method::GET,
            uri: '/users?page=1',
        );

        $users = [
            ['name' => 'Jonathan'],
            ['name' => 'Taylor'],
            ['name' => 'Jeffrey'],
        ];

        $callable = static function () use ($users): array {
            return [
                'data' => array_slice($users, 0, 2),
            ];
        };

        $response = new InertiaResponse($request, 'User/Index', ['users' => $callable], 'app', '123');

        $page = $response->body;

        $expected = [
            'users' => [
                'data' => [
                    ['name' => 'Jonathan'],
                    ['name' => 'Taylor'],
                ],
            ],
        ];

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('User/Index', $page['component']);
        static::assertSame('/users?page=1', $page['url']);
        static::assertSame('123', $page['version']);

        static::assertSame(json_encode($expected), json_encode($page['props']));
    }

    // public function test_nested_lazy_resource_response(): void
    // {
    //     $request = Request::create('/users', 'GET', ['page' => 1]);
    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $users = Collection::make([
    //         new Fluent(['name' => 'Jonathan']),
    //         new Fluent(['name' => 'Taylor']),
    //         new Fluent(['name' => 'Jeffrey']),
    //     ]);

    //     $callable = static function () use ($users) {
    //         $page = new LengthAwarePaginator($users->take(2), $users->count(), 2);

    //         // nested array with ResourceCollection to resolve
    //         return [
    //             'users' => new class($page, JsonResource::class) extends ResourceCollection {},
    //         ];
    //     };

    //     $response = new Response('User/Index', ['something' => $callable], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $page = $response->getData();

    //     $expected = [
    //         'users' => [
    //             'data' => $users->take(2),
    //             'links' => [
    //                 'first' => '/?page=1',
    //                 'last' => '/?page=2',
    //                 'prev' => null,
    //                 'next' => '/?page=2',
    //             ],
    //             'meta' => [
    //                 'current_page' => 1,
    //                 'from' => 1,
    //                 'last_page' => 2,
    //                 'path' => '/',
    //                 'per_page' => 2,
    //                 'to' => 2,
    //                 'total' => 3,
    //             ],
    //         ],
    //     ];

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $this->assertSame('User/Index', $page->component);
    //     $this->assertSame('/users?page=1', $page->url);
    //     $this->assertSame('123', $page->version);
    //     tap($page->props->something->users, function ($users) use ($expected) {
    //         $this->assertSame(json_encode($expected['users']['data']), json_encode($users->data));
    //         $this->assertSame(json_encode($expected['users']['links']), json_encode($users->links));
    //         $this->assertSame('/', $users->meta->path);
    //     });
    // }

    // public function test_arrayable_prop_response(): void
    // {
    //     $request = Request::create('/user/123', 'GET');
    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $resource = FakeResource::make(['name' => 'Jonathan']);

    //     $response = new Response('User/Edit', ['user' => $resource], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $page = $response->getData();

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $this->assertSame('User/Edit', $page->component);
    //     $this->assertSame('Jonathan', $page->props->user->name);
    //     $this->assertSame('/user/123', $page->url);
    //     $this->assertSame('123', $page->version);
    // }

    // public function test_promise_props_are_resolved(): void
    // {
    //     $request = Request::create('/user/123', 'GET');
    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $user = (object) ['name' => 'Jonathan'];

    //     $promise = Mockery::mock('GuzzleHttp\Promise\PromiseInterface')
    //         ->shouldReceive('wait')
    //         ->andReturn($user)
    //         ->mock();

    //     $response = new Response('User/Edit', ['user' => $promise], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $page = $response->getData();

    //     $this->assertInstanceOf(JsonResponse::class, $response);
    //     $this->assertSame('User/Edit', $page->component);
    //     $this->assertSame('Jonathan', $page->props->user->name);
    //     $this->assertSame('/user/123', $page->url);
    //     $this->assertSame('123', $page->version);
    // }

    public function test_xhr_partial_response(): void
    {
        $request = $this->createInertiaRequest(Method::GET, '/user/123', [
            Header::PARTIAL_COMPONENT => 'User/Edit',
            Header::PARTIAL_ONLY => 'partial',
        ]);

        $user = (object) ['name' => 'Jonathan'];
        $response = new InertiaResponse(
            $request,
            'User/Edit',
            ['user' => $user, 'partial' => 'partial-data'],
            'app',
            '123',
        );

        $page = $response->body;

        $props = $page['props'];

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('User/Edit', $page['component']);
        static::assertFalse(isset($props['user']));
        static::assertCount(1, $props);
        static::assertSame('partial-data', $page['props']['partial']);
        static::assertSame('/user/123', $page['url']);
        static::assertSame('123', $page['version']);
    }

    public function test_exclude_props_from_partial_response(): void
    {
        $request = $this->createInertiaRequest(Method::GET, '/user/123', [
            Header::PARTIAL_COMPONENT => 'User/Edit',
            Header::PARTIAL_EXCEPT => 'user',
        ]);

        $user = (object) ['name' => 'Jonathan'];
        $response = new InertiaResponse(
            $request,
            'User/Edit',
            [
                'user' => $user,
                'partial' => 'partial-data',
            ],
            'app',
            '123',
        );

        $page = $response->body;

        $props = $page['props'];

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('User/Edit', $page['component']);
        static::assertFalse(isset($props['user']));
        static::assertCount(1, $props);
        static::assertSame('partial-data', $page['props']['partial']);
        static::assertSame('/user/123', $page['url']);
        static::assertSame('123', $page['version']);
    }

    public function test_lazy_props_are_not_included_by_default(): void
    {
        $request = $this->createInertiaRequest(Method::GET, '/users');

        $lazyProp = new LazyProp(fn() => 'A lazy value');

        $response = new InertiaResponse($request, 'Users', ['users' => [], 'lazy' => $lazyProp], 'app', '123');
        $page = $response->body;

        static::assertSame([], $page['props']['users']);
        static::assertFalse(array_key_exists('lazy', $page['props']));
    }

    public function test_lazy_props_are_included_in_partial_reload(): void
    {
        $request = $this->createInertiaRequest(Method::GET, '/users', [
            Header::PARTIAL_COMPONENT => 'Users',
            Header::PARTIAL_ONLY => 'lazy',
        ]);

        $lazyProp = new LazyProp(function (): string {
            return 'A lazy value';
        });

        $response = new InertiaResponse($request, 'Users', ['users' => [], 'lazy' => $lazyProp], 'app', '123');
        $page = $response->body;

        static::assertFalse(array_key_exists('users', $page['props']));
        static::assertSame('A lazy value', $page['props']['lazy']);
    }

    public function test_always_props_are_included_on_partial_reload(): void
    {
        $request = $this->createInertiaRequest(Method::GET, '/user/123', [
            Header::PARTIAL_COMPONENT => 'User/Edit',
            Header::PARTIAL_ONLY => 'data',
        ]);

        $props = [
            'user' => new LazyProp(function (): array {
                return [
                    'name' => 'Jonathan Reinink',
                    'email' => 'jonathan@example.com',
                ];
            }),
            'data' => [
                'name' => 'Taylor Otwell',
            ],
            'errors' => new AlwaysProp(function (): array {
                return [
                    'name' => 'The email field is required.',
                ];
            }),
        ];

        $response = new InertiaResponse($request, 'User/Edit', $props, 'app', '123');
        $page = $response->body;

        static::assertSame('The email field is required.', $page['props']['errors']['name']);
        static::assertSame('Taylor Otwell', $page['props']['data']['name']);
        static::assertFalse(isset($page['props']['user']));
    }

    public function test_top_level_dot_props_get_unpacked(): void
    {
        $props = [
            'auth' => [
                'user' => [
                    'name' => 'Jonathan Reinink',
                ],
            ],
            'auth.user.can' => [
                'do.stuff' => true,
            ],
            'product' => ['name' => 'My example product'],
        ];

        $request = $this->createInertiaRequest(
            method: Method::GET,
            uri: '/products/123',
        );

        $response = new InertiaResponse($request, 'User/Edit', $props, 'app', '123');

        $page = $response->body;

        $user = $page['props']['auth']['user'];
        static::assertSame('Jonathan Reinink', $user['name']);
        static::assertTrue($user['can']['do.stuff']);
        static::assertFalse(array_key_exists('auth.user.can', $page['props']));
    }

    public function test_nested_dot_props_do_not_get_unpacked(): void
    {
        $props = [
            'auth' => [
                'user.can' => [
                    'do.stuff' => true,
                ],
                'user' => [
                    'name' => 'Jonathan Reinink',
                ],
            ],
            'product' => ['name' => 'My example product'],
        ];

        $request = $this->createInertiaRequest(
            method: Method::GET,
            uri: '/products/123',
        );

        $response = new InertiaResponse($request, 'User/Edit', $props, 'app', '123');
        $page = $response->body;

        $auth = $page['props']['auth'];
        static::assertSame('Jonathan Reinink', $auth['user']['name']);
        static::assertTrue($auth['user.can']['do.stuff']);
        static::assertFalse(array_key_exists('can', $auth));
    }

    // public function test_responsable_with_invalid_key(): void
    // {
    //     $request = Request::create('/user/123', 'GET');
    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $resource = new FakeResource(["\x00*\x00_invalid_key" => 'for object']);

    //     $response = new Response('User/Edit', ['resource' => $resource], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $page = $response->getData(true);

    //     $this->assertSame(
    //         ["\x00*\x00_invalid_key" => 'for object'],
    //         $page['props']['resource']
    //     );
    // }

    // public function test_the_page_url_is_prefixed_with_the_proxy_prefix(): void
    // {
    //     if (version_compare(app()->version(), '7', '<')) {
    //         $this->markTestSkipped('This test requires Laravel 7 or higher.');
    //     }

    //     Request::setTrustedProxies(['1.2.3.4'], Request::HEADER_X_FORWARDED_PREFIX);

    //     $request = Request::create('/user/123', 'GET');
    //     $request->server->set('REMOTE_ADDR', '1.2.3.4');
    //     $request->headers->set('X_FORWARDED_PREFIX', '/sub/directory');

    //     $user = ['name' => 'Jonathan'];
    //     $response = new Response('User/Edit', ['user' => $user], 'app', '123');
    //     $response = $response->toResponse($request);
    //     $view = $response->getOriginalContent();
    //     $page = $view->getData()['page'];

    //     $this->assertInstanceOf(BaseResponse::class, $response);
    //     $this->assertInstanceOf(View::class, $view);

    //     $this->assertSame('/sub/directory/user/123', $page['url']);
    // }

    // public function test_the_page_url_doesnt_double_up(): void
    // {
    //     $request = Request::create('/subpath/product/123', 'GET', [], [], [], [
    //         'SCRIPT_FILENAME' => '/project/public/index.php',
    //         'SCRIPT_NAME' => '/subpath/index.php',
    //     ]);
    //     $request->headers->add(['X-Inertia' => 'true']);

    //     $response = new Response('Product/Show', []);
    //     $response = $response->toResponse($request);
    //     $page = $response->getData();

    //     $this->assertSame('/subpath/product/123', $page->url);
    // }

    public function test_prop_as_basic_array(): void
    {
        $request = new GenericRequest(Method::GET, '/years');

        $response = new InertiaResponse($request, 'Years', ['years' => [2022, 2023, 2024]], 'app', '123');

        $view = $response->body;
        $page = $view->get('pageData');

        static::assertSame([2022, 2023, 2024], $page['props']['years']);
    }

    public function test_dot_notation_props_are_merged_with_shared_props(): void
    {
        $request = new GenericRequest(Method::GET, '/years');

        $response = new InertiaResponse(
            $request,
            'Test',
            [
                'auth' => ['user' => ['name' => 'Jonathan']], // shared prop
                'auth.user.is_super' => true,
            ],
            'app',
            '123',
        );

        $view = $response->body;
        $page = $view->get('pageData');

        static::assertSame([
            'auth' => [
                'user' => [
                    'name' => 'Jonathan',
                    'is_super' => true,
                ],
            ],
        ], $page['props']);
    }

    public function test_dot_notation_props_are_merged_with_lazy_shared_props(): void
    {
        $request = new GenericRequest(Method::GET, '/years');

        $response = new InertiaResponse(
            $request,
            'Test',
            [
                'auth' => function (): array {
                    return ['user' => ['name' => 'Jonathan']];
                },
                'auth.user.is_super' => true,
            ],
            'app',
            '123',
        );

        /** @var InertiaBaseView */
        $view = $response->body;
        $page = $view->get('pageData');

        static::assertSame([
            'auth' => [
                'user' => [
                    'name' => 'Jonathan',
                    'is_super' => true,
                ],
            ],
        ], $page['props']);
    }

    public function test_dot_notation_props_are_merged_with_other_dot_notation_props(): void
    {
        $request = new GenericRequest(Method::GET, '/years');

        $response = new InertiaResponse(
            $request,
            'Test',
            [
                'auth.user' => ['name' => 'Jonathan'],
                'auth.user.is_super' => true,
            ],
            'app',
            '123',
        );

        $view = $response->body;
        $page = $view->get('pageData');

        static::assertSame([
            'auth' => [
                'user' => [
                    'name' => 'Jonathan',
                    'is_super' => true,
                ],
            ],
        ], $page['props']);
    }

    public function test_server_response_with_deferred_prop(): void
    {
        $request = $this->createInertiaRequest(Method::GET, '/user/123');

        $user = ['name' => 'Jonathan'];
        $response = new InertiaResponse(
            request: $request,
            page: 'User/Edit',
            props: [
                'user' => $user,
                'foo' => new DeferProp(function () {
                    return 'bar';
                }, group: 'default'),
            ],
            rootView: 'app',
            version: '123',
        );

        $pageData = $response->body;

        static::assertIsArray($pageData);

        static::assertSame('User/Edit', $pageData['component']);
        static::assertSame('Jonathan', $pageData['props']['user']['name']);
        static::assertSame('/user/123', $pageData['url']);
        static::assertSame('123', $pageData['version']);
        static::assertSame([
            'default' => ['foo'],
        ], $pageData['deferredProps']);

        // $this->assertFalse($pageData['clearHistory']);
        // $this->assertFalse($pageData['encryptHistory']);
    }

    public function test_server_response_with_deferred_prop_and_multiple_groups(): void
    {
        $request = $this->createInertiaRequest(Method::GET, '/user/123');

        $user = ['name' => 'Jonathan'];
        $response = new InertiaResponse(
            request: $request,
            page: 'User/Edit',
            props: [
                'user' => $user,
                'foo' => new DeferProp(function (): string {
                    return 'foo value';
                }, 'default'),
                'bar' => new DeferProp(function (): string {
                    return 'bar value';
                }, 'default'),
                'baz' => new DeferProp(function (): string {
                    return 'baz value';
                }, 'custom'),
            ],
            rootView: 'app',
            version: '123',
        );

        $page = $response->body;

        static::assertSame('User/Edit', $page['component']);
        static::assertSame('Jonathan', $page['props']['user']['name']);
        static::assertSame('/user/123', $page['url']);
        static::assertSame('123', $page['version']);
        static::assertSame([
            'default' => ['foo', 'bar'],
            'custom' => ['baz'],
        ], $page['deferredProps']);

        // $this->assertFalse($page['clearHistory']);
        // $this->assertFalse($page['encryptHistory']);
        // $this->assertSame('<div id="app" data-page="{&quot;component&quot;:&quot;User\/Edit&quot;,&quot;props&quot;:{&quot;user&quot;:{&quot;name&quot;:&quot;Jonathan&quot;}},&quot;url&quot;:&quot;\/user\/123&quot;,&quot;version&quot;:&quot;123&quot;,&quot;clearHistory&quot;:false,&quot;encryptHistory&quot;:false,&quot;deferredProps&quot;:{&quot;default&quot;:[&quot;foo&quot;,&quot;bar&quot;],&quot;custom&quot;:[&quot;baz&quot;]}}"></div>', $view->render());
    }
}
