<?php

namespace NeoIsRecursive\Inertia\Props;

use Closure;
use NeoIsRecursive\Inertia\Concerns\IsMergeableProp;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;

use function Tempest\invoke;

final class AlwaysProp implements MergeableProp
{
    use IsMergeableProp;

    public function __construct(
        public readonly MethodReflector|FunctionReflector|string|Closure|array $value,
        public private(set) bool $shouldMerge = false
    ) {}

    public function __invoke()
    {
        return is_callable($this->value) ? invoke($this->value) : $this->value;
    }
}
