<?php

namespace NeoIsRecursive\Inertia;

use Closure;

final readonly class InertiaConfig
{
    public function __construct(
        public string|Closure $version,
        public string $rootView,
        public Closure $getSharedProps,
    ) {}
}
