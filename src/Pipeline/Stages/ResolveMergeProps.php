<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline\Stages;

use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use NeoIsRecursive\Inertia\Pipeline\PropPipelineContext;
use NeoIsRecursive\Inertia\Pipeline\PropStage;
use NeoIsRecursive\Inertia\Props\ScrollProp;
use NeoIsRecursive\Inertia\Support\Header;

use function Tempest\Support\arr;

final readonly class ResolveMergeProps implements PropStage
{
    public function __invoke(PropPipelineContext $context): PropPipelineContext
    {
        $resetProps = arr(explode(separator: ',', string: $context->request->headers->get(Header::RESET) ?? ''));

        $mergeProps = arr($context->originalProps)
            ->filter(static fn($prop) => $prop instanceof MergeableProp && $prop->shouldMerge)
            ->filter(static fn($_, $key) => !$resetProps->contains($key))
            ->map(static fn(MergeableProp $prop, string|int $key) => $prop instanceof ScrollProp
                ? $prop->mergeKey($key)
                : $key)
            ->values();

        return $context->with(['mergeProps' => $mergeProps->isEmpty() ? null : $mergeProps->toArray()]);
    }
}
