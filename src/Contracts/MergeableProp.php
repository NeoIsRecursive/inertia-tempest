<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Contracts;

use NeoIsRecursive\Inertia\Props\PropContext;

interface MergeableProp
{
    public bool $shouldMerge { get; }

    public function merge(): static;

    public function resolveMergeMetadata(PropContext $context);
}
