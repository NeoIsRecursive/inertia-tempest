<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline\Stages;

use NeoIsRecursive\Inertia\Pipeline\PropPipelineContext;
use NeoIsRecursive\Inertia\Pipeline\PropStage;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\DeferProp;
use NeoIsRecursive\Inertia\Props\OptionalProp;
use NeoIsRecursive\Inertia\Support\Header;

final readonly class FilterProps implements PropStage
{
    public function __invoke(PropPipelineContext $context): PropPipelineContext
    {
        $always = array_filter($context->originalProps, static fn($prop) => $prop instanceof AlwaysProp);
        $partial = $this->resolvePartialProps($context);

        return $context->with(['renderableProps' => array_merge($always, $partial)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvePartialProps(PropPipelineContext $context): array
    {
        $headers = $context->request->headers;

        if (!$context->isPartial()) {
            return array_filter(
                $context->originalProps,
                static fn($prop) => !($prop instanceof OptionalProp || $prop instanceof DeferProp),
            );
        }

        $only = array_filter(explode(separator: ',', string: $headers->get(Header::PARTIAL_ONLY) ?? ''));
        $except = array_filter(explode(separator: ',', string: $headers->get(Header::PARTIAL_EXCEPT) ?? ''));

        $filtered = $only ? array_intersect_key($context->originalProps, array_flip($only)) : $context->originalProps;

        return array_filter($filtered, static fn($key) => !in_array($key, $except, true), ARRAY_FILTER_USE_KEY);
    }
}
