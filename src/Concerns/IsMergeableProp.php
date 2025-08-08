<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Concerns;

/**
 * @phpstan-require-implements \NeoIsRecursive\Inertia\Contracts\MergeableProp
 */
trait IsMergeableProp
{
    public function merge(): self
    {
        $this->shouldMerge = true;

        return $this;
    }
}
