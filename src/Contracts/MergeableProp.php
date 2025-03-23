<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Contracts;

interface MergeableProp
{
    public bool $shouldMerge {
        get;
    }

    public function merge(): self;
}
