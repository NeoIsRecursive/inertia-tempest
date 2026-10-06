<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

/** @require-implements \NeoIsRecursive\Inertia\Contracts\MergeableProp */
trait IsMergeable
{
    public bool $shouldMerge = false;

    public function merge(): static
    {
        return clone($this, [
            'shouldMerge' => true,
        ]);
    }

    public function resolveMergeMetadata(PropContext $context): array
    {
        if (!$this->shouldMerge)
            return [];

        return [
            'mergeProps' => [
                $context->key,
            ],
        ];
    }
}
