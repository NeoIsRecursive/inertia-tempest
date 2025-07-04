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
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Response;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

use function Tempest\get;

final class ResponseTest extends TestCase
{
    public function test_server_response(): void
    {
        $request = new GenericRequest(Method::GET, uri: '/user/123');

        $user = ['name' => 'Jonathan'];
        $response = new InertiaResponse(
            $request,
            component: 'User/Edit',
            props: ['user' => $user],
            rootView: __DIR__ . '/../Fixtures/root.view.php',
            version: '123',
        );
        $view = $response->body;
        $page = $view->get(key: 'pageData');

        static::assertInstanceOf(Response::class, $response);
        static::assertInstanceOf(View::class, $view);

        static::assertSame(
            expected: 'User/Edit',
            actual: $page['component'],
        );
        static::assertSame(
            expected: 'Jonathan',
            actual: $page['props']['user']['name'],
        );
        static::assertSame(
            expected: '/user/123',
            actual: $page['url'],
        );
        static::assertSame(
            expected: '123',
            actual: $page['version'],
        );
        static::assertSame(
            expected: '<main>     <div id="app" data-page="{&quot;component&quot;:&quot;User\/Edit&quot;,&quot;props&quot;:{&quot;user&quot;:{&quot;name&quot;:&quot;Jonathan&quot;}},&quot;url&quot;:&quot;\/user\/123&quot;,&quot;version&quot;:&quot;123&quot;,&quot;clearHistory&quot;:false,&quot;encryptHistory&quot;:false}"></div></main>',
            actual: get(ViewRenderer::class)->render($view),
        );
    }

    public function test_xhr_response(): void
    {
        $request = $this->createInertiaRequest(Method::GET, uri: '/user/123');

        $user = ['name' => 'Jonathan'];
        $response = new InertiaResponse(
            $request,
            component: 'User/Edit',
            props: ['user' => $user],
            rootView: 'app',
            version: '123',
        );

        $page = $response->body;

        static::assertInstanceOf(Response::class, $response);

        static::assertSame(
            expected: 'User/Edit',
            actual: $page['component'],
        );
        static::assertSame(
            expected: 'Jonathan',
            actual: $page['props']['user']['name'],
        );
        static::assertSame(
            expected: '/user/123',
            actual: $page['url'],
        );
        static::assertSame(
            expected: '123',
            actual: $page['version'],
        );
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
                'data' => array_slice($users, offset: 0, length: 2),
            ];
        };

        $response = new InertiaResponse(
            $request,
            component: 'User/Index',
            props: ['users' => $callable],
            rootView: 'app',
            version: '123',
        );

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
        static::assertSame(
            expected: 'User/Index',
            actual: $page['component'],
        );
        static::assertSame(
            expected: '/users?page=1',
            actual: $page['url'],
        );
        static::assertSame(
            expected: '123',
            actual: $page['version'],
        );

        static::assertSame(
            expected: json_encode($expected),
            actual: json_encode($page['props']),
        );
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
        $request = $this->createInertiaRequest(Method::GET, uri: '/user/123', headers: [
            Header::PARTIAL_COMPONENT => 'User/Edit',
            Header::PARTIAL_ONLY => 'partial',
        ]);

        $user = (object) ['name' => 'Jonathan'];
        $response = new InertiaResponse(
            $request,
            component: 'User/Edit',
            props: ['user' => $user, 'partial' => 'partial-data'],
            rootView: 'app',
            version: '123',
        );

        $page = $response->body;

        $props = $page['props'];

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(
            expected: 'User/Edit',
            actual: $page['component'],
        );
        static::assertFalse(isset($props['user']));
        static::assertCount(
            expectedCount: 1,
            haystack: $props,
        );
        static::assertSame(
            expected: 'partial-data',
            actual: $page['props']['partial'],
        );
        static::assertSame(
            expected: '/user/123',
            actual: $page['url'],
        );
        static::assertSame(
            expected: '123',
            actual: $page['version'],
        );
    }

    public function test_exclude_props_from_partial_response(): void
    {
        $request = $this->createInertiaRequest(Method::GET, uri: '/user/123', headers: [
            Header::PARTIAL_COMPONENT => 'User/Edit',
            Header::PARTIAL_EXCEPT => 'user',
        ]);

        $user = (object) ['name' => 'Jonathan'];
        $response = new InertiaResponse(
            $request,
            component: 'User/Edit',
            props: [
                'user' => $user,
                'partial' => 'partial-data',
            ],
            rootView: 'app',
            version: '123',
        );

        $page = $response->body;

        $props = $page['props'];

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(
            expected: 'User/Edit',
            actual: $page['component'],
        );
        static::assertFalse(isset($props['user']));
        static::assertCount(
            expectedCount: 1,
            haystack: $props,
        );
        static::assertSame(
            expected: 'partial-data',
            actual: $page['props']['partial'],
        );
        static::assertSame(
            expected: '/user/123',
            actual: $page['url'],
        );
        static::assertSame(
            expected: '123',
            actual: $page['version'],
        );
    }

    public function test_lazy_props_are_not_included_by_default(): void
    {
        $request = $this->createInertiaRequest(Method::GET, uri: '/users');

        $lazyProp = new LazyProp(fn() => 'A lazy value');

        $response = new InertiaResponse(
            $request,
            component: 'Users',
            props: ['users' => [], 'lazy' => $lazyProp],
            rootView: 'app',
            version: '123',
        );
        $page = $response->body;

        static::assertSame(
            expected: [],
            actual: $page['props']['users'],
        );
        static::assertFalse(array_key_exists(
            key: 'lazy',
            array: $page['props'],
        ));
    }

    public function test_lazy_props_are_included_in_partial_reload(): void
    {
        $request = $this->createInertiaRequest(Method::GET, uri: '/users', headers: [
            Header::PARTIAL_COMPONENT => 'Users',
            Header::PARTIAL_ONLY => 'lazy',
        ]);

        $lazyProp = new LazyProp(function (): string {
            return 'A lazy value';
        });

        $response = new InertiaResponse(
            $request,
            component: 'Users',
            props: ['users' => [], 'lazy' => $lazyProp],
            rootView: 'app',
            version: '123',
        );
        $page = $response->body;

        static::assertFalse(array_key_exists(
            key: 'users',
            array: $page['props'],
        ));
        static::assertSame(
            expected: 'A lazy value',
            actual: $page['props']['lazy'],
        );
    }

    public function test_always_props_are_included_on_partial_reload(): void
    {
        $request = $this->createInertiaRequest(Method::GET, uri: '/user/123', headers: [
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

        $response = new InertiaResponse($request, component: 'User/Edit', props: $props, rootView: 'app', version: '123');
        $page = $response->body;

        static::assertSame(
            expected: 'The email field is required.',
            actual: $page['props']['errors']['name'],
        );
        static::assertSame(
            expected: 'Taylor Otwell',
            actual: $page['props']['data']['name'],
        );
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

        $response = new InertiaResponse($request, component: 'User/Edit', props: $props, rootView: 'app', version: '123');

        $page = $response->body;

        $user = $page['props']['auth']['user'];
        static::assertSame(
            expected: 'Jonathan Reinink',
            actual: $user['name'],
        );
        static::assertTrue($user['can']['do.stuff']);
        static::assertFalse(array_key_exists(
            key: 'auth.user.can',
            array: $page['props'],
        ));
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

        $response = new InertiaResponse($request, component: 'User/Edit', props: $props, rootView: 'app', version: '123');
        $page = $response->body;

        $auth = $page['props']['auth'];
        static::assertSame(
            expected: 'Jonathan Reinink',
            actual: $auth['user']['name'],
        );
        static::assertTrue($auth['user.can']['do.stuff']);
        static::assertFalse(array_key_exists(
            key: 'can',
            array: $auth,
        ));
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
        $request = new GenericRequest(Method::GET, uri: '/years');

        $response = new InertiaResponse(
            $request,
            component: 'Years',
            props: ['years' => [2022, 2023, 2024]],
            rootView: 'app',
            version: '123',
        );

        $view = $response->body;
        $page = $view->get(key: 'pageData');

        static::assertSame(
            expected: [2022, 2023, 2024],
            actual: $page['props']['years'],
        );
    }

    public function test_dot_notation_props_are_merged_with_shared_props(): void
    {
        $request = new GenericRequest(Method::GET, uri: '/years');

        $response = new InertiaResponse(
            $request,
            component: 'Test',
            props: [
                'auth' => ['user' => ['name' => 'Jonathan']], // shared prop
                'auth.user.is_super' => true,
            ],
            rootView: 'app',
            version: '123',
        );

        $view = $response->body;
        $page = $view->get(key: 'pageData');

        static::assertSame(
            expected: [
                'auth' => [
                    'user' => [
                        'name' => 'Jonathan',
                        'is_super' => true,
                    ],
                ],
            ],
            actual: $page['props'],
        );
    }

    public function test_dot_notation_props_are_merged_with_lazy_shared_props(): void
    {
        $request = new GenericRequest(Method::GET, uri: '/years');

        $response = new InertiaResponse(
            $request,
            component: 'Test',
            props: [
                'auth' => function (): array {
                    return ['user' => ['name' => 'Jonathan']];
                },
                'auth.user.is_super' => true,
            ],
            rootView: 'app',
            version: '123',
        );

        /** @var InertiaBaseView */
        $view = $response->body;
        $page = $view->get(key: 'pageData');

        static::assertSame(
            expected: [
                'auth' => [
                    'user' => [
                        'name' => 'Jonathan',
                        'is_super' => true,
                    ],
                ],
            ],
            actual: $page['props'],
        );
    }

    public function test_dot_notation_props_are_merged_with_other_dot_notation_props(): void
    {
        $request = new GenericRequest(Method::GET, uri: '/years');

        $response = new InertiaResponse(
            $request,
            component: 'Test',
            props: [
                'auth.user' => ['name' => 'Jonathan'],
                'auth.user.is_super' => true,
            ],
            rootView: 'app',
            version: '123',
        );

        $view = $response->body;
        $page = $view->get(key: 'pageData');

        static::assertSame(
            expected: [
                'auth' => [
                    'user' => [
                        'name' => 'Jonathan',
                        'is_super' => true,
                    ],
                ],
            ],
            actual: $page['props'],
        );
    }

    public function test_server_response_with_deferred_prop(): void
    {
        $request = $this->createInertiaRequest(Method::GET, uri: '/user/123');

        $user = ['name' => 'Jonathan'];
        $response = new InertiaResponse(
            request: $request,
            component: 'User/Edit',
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

        static::assertSame(
            expected: 'User/Edit',
            actual: $pageData['component'],
        );
        static::assertSame(
            expected: 'Jonathan',
            actual: $pageData['props']['user']['name'],
        );
        static::assertSame(
            expected: '/user/123',
            actual: $pageData['url'],
        );
        static::assertSame(
            expected: '123',
            actual: $pageData['version'],
        );
        static::assertSame(
            expected: [
                'default' => ['foo'],
            ],
            actual: $pageData['deferredProps'],
        );

        // $this->assertFalse($pageData['clearHistory']);
        // $this->assertFalse($pageData['encryptHistory']);
    }

    public function test_server_response_with_deferred_prop_and_multiple_groups(): void
    {
        $request = $this->createInertiaRequest(Method::GET, uri: '/user/123');

        $user = ['name' => 'Jonathan'];
        $response = new InertiaResponse(
            request: $request,
            component: 'User/Edit',
            props: [
                'user' => $user,
                'foo' => new DeferProp(function (): string {
                    return 'foo value';
                }, group: 'default'),
                'bar' => new DeferProp(function (): string {
                    return 'bar value';
                }, group: 'default'),
                'baz' => new DeferProp(function (): string {
                    return 'baz value';
                }, group: 'custom'),
            ],
            rootView: 'app',
            version: '123',
        );

        $page = $response->body;

        static::assertSame(
            expected: 'User/Edit',
            actual: $page['component'],
        );
        static::assertSame(
            expected: 'Jonathan',
            actual: $page['props']['user']['name'],
        );
        static::assertSame(
            expected: '/user/123',
            actual: $page['url'],
        );
        static::assertSame(
            expected: '123',
            actual: $page['version'],
        );
        static::assertSame(
            expected: [
                'default' => ['foo', 'bar'],
                'custom' => ['baz'],
            ],
            actual: $page['deferredProps'],
        );

        // $this->assertFalse($page['clearHistory']);
        // $this->assertFalse($page['encryptHistory']);
        // $this->assertSame('<div id="app" data-page="{&quot;component&quot;:&quot;User\/Edit&quot;,&quot;props&quot;:{&quot;user&quot;:{&quot;name&quot;:&quot;Jonathan&quot;}},&quot;url&quot;:&quot;\/user\/123&quot;,&quot;version&quot;:&quot;123&quot;,&quot;clearHistory&quot;:false,&quot;encryptHistory&quot;:false,&quot;deferredProps&quot;:{&quot;default&quot;:[&quot;foo&quot;,&quot;bar&quot;],&quot;custom&quot;:[&quot;baz&quot;]}}"></div>', $view->render());
    }
}
