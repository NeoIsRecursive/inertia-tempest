<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Fixtures;

use NeoIsRecursive\Inertia\Inertia;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use Tempest\Router\Get;

use function NeoIsRecursive\Inertia\inertia;

final readonly class TestController
{

    public function index()
    {
        return inertia('Index');
    }

    #[Get(uri: '/can-share-props-from-any-where')]
    public function testCanSharePropsFromAnyWhere(Inertia $inertia)
    {
        $inertia->share('foo', 'bar');

        $inertia->share([
            'baz' => 'qux',
        ]);

        return inertia('User/Edit');
    }

    #[Get(uri: '/all-sorts-of-props')]
    public function testAllSortsOfProps(Inertia $inertia)
    {
        $inertia->share('foo', 'bar');

        return inertia('User/Edit', [
            new AlwaysProp(fn() => 'baz'),
        ]);
    }
}
