<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use NeoIsRecursive\Inertia\Concerns\IsMergeableProp;
use NeoIsRecursive\Inertia\Contracts\CallableProp;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use Override;

use function Tempest\invoke;

final class AlwaysProp implements CallableProp, MergeableProp
{
    use IsMergeableProp;

    public function __construct(
        public readonly mixed $value,
        public private(set) bool $shouldMerge = false,
    ) {}

    #[Override]
    public function __invoke(): mixed
    {
        return is_callable($this->value) ? invoke($this->value) : $this->value;
    }
}
