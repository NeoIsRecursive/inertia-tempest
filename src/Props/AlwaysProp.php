<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use NeoIsRecursive\Inertia\Concerns\IsCallableProp;
use NeoIsRecursive\Inertia\Concerns\IsMergeableProp;
use NeoIsRecursive\Inertia\Contracts\CallableProp;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use Override;

final class AlwaysProp implements CallableProp, MergeableProp
{
    use IsMergeableProp;
    use IsCallableProp;

    public function __construct(
        public readonly mixed $value,
        public private(set) bool $shouldMerge = false,
    ) {}

    #[Override]
    public function __invoke(): mixed
    {
        return $this->resolveCallablePropValue($this->value);
    }
}
