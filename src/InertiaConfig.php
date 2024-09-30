<?php

namespace NeoIsRecursive\Inertia;

use Closure;

final readonly class InertiaConfig
{
    public function __construct(
        public string $version,
        public string $rootView,
        public Closure $getSharedProps,
    ) {}
}
