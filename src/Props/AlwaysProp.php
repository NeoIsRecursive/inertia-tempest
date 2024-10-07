<?php

namespace NeoIsRecursive\Inertia\Props;

use Closure;

use function Tempest\invoke;

final readonly class AlwaysProp
{
    public function __construct(public Closure $value) {}

    public function __invoke()
    {
        return is_callable($this->value) ? invoke($this->value) : $this->value;
    }
}
