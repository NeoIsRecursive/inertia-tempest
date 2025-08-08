<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use Closure;
use NeoIsRecursive\Inertia\Concerns\IsMergeableProp;
use NeoIsRecursive\Inertia\Contracts\CallableProp;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use Override;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;

use function Tempest\invoke;

final class AlwaysProp implements CallableProp, MergeableProp
{
    use IsMergeableProp;

    public function __construct(
        public readonly MethodReflector|FunctionReflector|string|Closure|array $value,
        public private(set) bool $shouldMerge = false,
    ) {}

    #[Override]
    public function __invoke(): mixed
    {
        return is_callable($this->value) ? invoke($this->value) : $this->value;
    }
}
