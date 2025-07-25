<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use Closure;
use NeoIsRecursive\Inertia\Concerns\IsMergeableProp;
use NeoIsRecursive\Inertia\Contracts\CallableProp;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;

use function Tempest\invoke;

final class DeferProp implements CallableProp, MergeableProp
{
    use IsMergeableProp;

    public function __construct(
        public MethodReflector|FunctionReflector|string|array|Closure $callback,
        public string $group = 'default',
        public private(set) bool $shouldMerge = false,
    ) {}

    public function __invoke(): mixed
    {
        return invoke($this->callback);
    }
}
