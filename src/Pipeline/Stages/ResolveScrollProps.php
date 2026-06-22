<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline\Stages;

use NeoIsRecursive\Inertia\Pipeline\PropPipelineContext;
use NeoIsRecursive\Inertia\Pipeline\PropStage;
use NeoIsRecursive\Inertia\Props\ScrollProp;

use function Tempest\Support\arr;

final readonly class ResolveScrollProps implements PropStage
{
    public function __invoke(PropPipelineContext $context): PropPipelineContext
    {
        $scrollProps = arr($context->renderableProps)
            ->filter(static fn($prop) => $prop instanceof ScrollProp)
            ->map(static fn(ScrollProp $prop) => $prop->metadata());

        return $context->with(['scrollProps' => $scrollProps->isEmpty() ? null : $scrollProps->toArray()]);
    }
}
