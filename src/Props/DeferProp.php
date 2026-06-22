<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use Closure;
use NeoIsRecursive\Inertia\Concerns\IsCallableProp;
use NeoIsRecursive\Inertia\Concerns\IsMergeableProp;
use NeoIsRecursive\Inertia\Contracts\CallableProp;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use Override;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;

final class DeferProp implements CallableProp, MergeableProp
{
    use IsMergeableProp;
    use IsCallableProp;

    public function __construct(
        public MethodReflector|FunctionReflector|Closure $callback,
        public string $group = 'default',
        public private(set) bool $shouldMerge = false,
    ) {}

    #[Override]
    public function __invoke(): mixed
    {
        return $this->resolveCallablePropValue($this->callback);
    }
}
