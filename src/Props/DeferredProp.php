<?php

namespace NeoIsRecursive\Inertia\Props;

use Closure;
use NeoIsRecursive\Inertia\Concerns\IsMergeableProp;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;

use function Tempest\invoke;

final class DeferredProp implements MergeableProp
{
    use IsMergeableProp;

    public function __construct(
        public MethodReflector|FunctionReflector|string|array|Closure $callback,
        public private(set) bool $shouldMerge = false
    ) {}

    public function __invoke()
    {
        return invoke($this->callback);
    }
}
