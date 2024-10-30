<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Fixtures;

use NeoIsRecursive\Inertia\Inertia;
use Tempest\Http\Get;

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

        return inertia('User/Edit');
    }
}
