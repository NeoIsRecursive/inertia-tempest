<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline;

use NeoIsRecursive\Inertia\Pipeline\Stages\EvaluateProps;
use NeoIsRecursive\Inertia\Pipeline\Stages\FilterProps;
use NeoIsRecursive\Inertia\Pipeline\Stages\ResolveDeferredProps;
use NeoIsRecursive\Inertia\Pipeline\Stages\ResolveMergeProps;
use NeoIsRecursive\Inertia\Pipeline\Stages\ResolveOnceProps;
use NeoIsRecursive\Inertia\Pipeline\Stages\ResolveScrollProps;
use Tempest\Http\Request;

final readonly class PropPipeline
{
    /**
     * @param array<string, mixed> $props
     */
    public function process(array $props, Request $request, string $component): ProcessedProps
    {
        $context = new PropPipelineContext(request: $request, component: $component, originalProps: $props)
            |> new FilterProps()
            |> new EvaluateProps()
            |> new ResolveDeferredProps()
            |> new ResolveMergeProps()
            |> new ResolveScrollProps()
            |> new ResolveOnceProps();

        return new ProcessedProps(
            props: $context->evaluatedProps,
            deferredProps: $context->deferredProps,
            mergeProps: $context->mergeProps,
            scrollProps: $context->scrollProps,
            onceProps: $context->onceProps,
        );
    }
}
