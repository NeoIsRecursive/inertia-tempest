<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline\Stages;

use NeoIsRecursive\Inertia\Contracts\Onceable;
use NeoIsRecursive\Inertia\Pipeline\PropPipelineContext;
use NeoIsRecursive\Inertia\Pipeline\PropStage;
use NeoIsRecursive\Inertia\Props\DeferProp;
use NeoIsRecursive\Inertia\Support\Header;

use function Tempest\Support\arr;

final readonly class ResolveDeferredProps implements PropStage
{
    public function __invoke(PropPipelineContext $context): PropPipelineContext
    {
        if ($context->isPartial()) {
            return $context->with(['deferredProps' => null]);
        }

        $loadedOnce = array_filter(explode(
            separator: ',',
            string: $context->request->headers->get(Header::EXCEPT_ONCE_PROPS) ?? '',
        ));

        $deferredProps = arr($context->originalProps)
            ->filter(
                static fn($prop, string|int $key) => !(
                    $prop instanceof Onceable
                    && $context->request->headers->has(Header::INERTIA)
                    && $prop->shouldResolveOnce()
                    && !$prop->shouldBeRefreshed()
                    && in_array($prop->getKey() ?? (string) $key, $loadedOnce, true)
                ),
            )
            ->filter(static fn($prop) => $prop instanceof DeferProp)
            ->map(static fn(DeferProp $prop, string|int $key) => [
                'group' => $prop->group,
                'key' => $key,
            ])
            ->groupBy(static fn($prop) => $prop['group'])
            ->map(static fn($group) => arr($group)->pluck(value: 'key')->toArray());

        return $context->with(['deferredProps' => $deferredProps->isEmpty() ? null : $deferredProps->toArray()]);
    }
}
