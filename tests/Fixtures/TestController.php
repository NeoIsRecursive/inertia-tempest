<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Fixtures;

use NeoIsRecursive\Inertia\Http\InertiaResponse;
use NeoIsRecursive\Inertia\Inertia;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use Tempest\Router\Get;

use function NeoIsRecursive\Inertia\inertia;

final readonly class TestController
{
    public function index(): InertiaResponse
    {
        return inertia(page: 'Index');
    }

    #[Get(uri: '/can-share-props-from-any-where')]
    public function testCanSharePropsFromAnyWhere(Inertia $inertia): InertiaResponse
    {
        $inertia->share(
            key: 'foo',
            value: 'bar',
        );

        $inertia->share([
            'baz' => 'qux',
        ]);

        return inertia(page: 'User/Edit');
    }

    #[Get(uri: '/all-sorts-of-props')]
    public function testAllSortsOfProps(Inertia $inertia): InertiaResponse
    {
        $inertia->share(
            key: 'foo',
            value: 'bar',
        );

        return inertia(
            page: 'User/Edit',
            props: [
                new AlwaysProp(fn() => 'baz'),
            ],
        );
    }
}
