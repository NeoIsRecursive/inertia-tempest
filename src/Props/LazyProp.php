<?php

namespace NeoIsRecursive\Inertia\Props;

use Closure;

use function Tempest\invoke;

final readonly class LazyProp
{
    public function __construct(public Closure $callback) {}

    public function __invoke()
    {
        return invoke($this->callback);
    }
}
