<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Fixtures;

use NeoIsRecursive\Inertia\Contracts\InertiaVersionResolver;
use Tempest\Container\Container;

final readonly class TestVersionResolver implements InertiaVersionResolver
{
    public function resolve(Container $container): string
    {
        return '123';
    }
}
