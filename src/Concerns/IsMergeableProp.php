<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Concerns;

use NeoIsRecursive\Inertia\Contracts\MergeableProp;

/**
 * @implements MergeableProp
 */
trait IsMergeableProp
{
    public function merge(): self
    {
        $this->shouldMerge = true;

        return $this;
    }
}
