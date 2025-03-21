<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Contracts;

use Tempest\Container\Container;

interface InertiaVersionResolver
{
    public function resolve(Container $container): string;
}
