<?php

namespace NeoIsRecursive\Inertia\Props;

use Closure;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;

use function Tempest\invoke;

final readonly class AlwaysProp
{
    public function __construct(public MethodReflector|FunctionReflector|string|Closure $value) {}

    public function __invoke()
    {
        return is_callable($this->value) ? invoke($this->value) : $this->value;
    }
}
