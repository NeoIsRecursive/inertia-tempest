<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Http\Component;
use NeoIsRecursive\Inertia\Http\InertiaResponse;
use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Props\ScrollProp;
use NeoIsRecursive\Inertia\Tests\Fixtures\TestVersionResolver;
use NeoIsRecursive\Inertia\Tests\TestCase;
use NeoIsRecursive\Inertia\Views\InertiaBaseView;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Support\Paginator\Paginator;
use Tempest\View\ViewRenderer;

use function Tempest\Container\get;
use function Tempest\Support\arr;

final class ScrollPropsTest extends TestCase
{
    public function test_scroll_props_renders_correctly()
    {
        $this->container->singleton(Request::class, new GenericRequest(Method::GET, uri: '/posts?page=1'));

        $response = new Component(name: 'Posts/Index', props: [
            'posts' => new ScrollProp(function () {
                $paginator = new Paginator(totalItems: 4, itemsPerPage: 2, currentPage: 1, maxLinks: 2);

                return $paginator->paginateWith(
                    callback: fn(int $limit, int $offset) => arr([
                        [
                            'id' => 1,
                            'title' => 'First Post',
                        ],
                        [
                            'id' => 2,
                            'title' => 'Second Post',
                        ],
                        [
                            'id' => 3,
                            'title' => 'Third Post',
                        ],
                        [
                            'id' => 4,
                            'title' => 'Fourth Post',
                        ],
                    ])->slice($offset, $limit)->toArray(),
                );
            }, 'page'),
        ]);

        /** @var InertiaBaseView */
        $view = $response->body;

        $expectedHtml = <<<'HTML'
                <main>
                    <script data-page="app" type="application/json">
                    {
                        "component": "Posts/Index",
                        "props": {
                            "user": null,
                            "errors": [],
                            "posts": {
                                "data": [
                                    {
                                        "id": 1,
                                        "title": "First Post"
                                    },
                                    {
                                        "id": 2,
                                        "title": "Second Post"
                                    }
                                ]
                            }
                        },
                        "url": "/posts?page=1",
                        "version": "123",
                        "mergeProps": [
                            "posts.data"
                        ],
                        "scrollProps": {
                            "posts": {
                                "pageName": "page",
                                "previousPage": null,
                                "nextPage": 2,
                                "currentPage": 1
                            }
                        }
                    }
                    </script>
                    <div id="app"></div>
                </main>
            HTML;

        static::assertSnippetsMatch(expected: $expectedHtml, actual: get(ViewRenderer::class)->render($view));
    }
}
