<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Concerns;

/**
 * @require-implements \NeoIsRecursive\Inertia\Contracts\MergeableProp
 */
trait IsMergeableProp
{
    public function merge(): static
    {
        $this->shouldMerge = true;

        return $this;
    }
}
