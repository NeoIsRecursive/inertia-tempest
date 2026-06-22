<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline\Stages;

use NeoIsRecursive\Inertia\Pipeline\PropPipelineContext;
use NeoIsRecursive\Inertia\Pipeline\PropStage;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\DeferProp;
use NeoIsRecursive\Inertia\Props\OptionalProp;
use NeoIsRecursive\Inertia\Support\Header;
use Tempest\Http\Request;

final readonly class FilterProps implements PropStage
{
    public function __invoke(PropPipelineContext $context): PropPipelineContext
    {
        $always = array_filter($context->originalProps, static fn(mixed $prop) => $prop instanceof AlwaysProp);
        $partial = $this->resolvePartialProps($context);

        $loadedOnce = self::parseHeader(Header::EXCEPT_ONCE_PROPS, $context->request);
        $only = self::parseHeader(Header::PARTIAL_ONLY, $context->request);

        $renderable = array_merge($always, $partial);

        return $context->with(['renderableProps' => $renderable]);
    }

    /**
     * @return list<string>
     */
    private static function parseHeader(string $header, Request $request): array
    {
        $values = array_filter(explode(separator: ',', string: $request->headers->get($header) ?? ''));

        return array_values($values);
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
                static fn(mixed $prop) => !($prop instanceof OptionalProp || $prop instanceof DeferProp),
            );
        }

        $only = self::parseHeader(Header::PARTIAL_ONLY, $context->request);
        $except = self::parseHeader(Header::PARTIAL_EXCEPT, $context->request);

        $filtered = $only ? array_intersect_key($context->originalProps, array_flip($only)) : $context->originalProps;

        return array_filter(
            $filtered,
            static fn(string|int $key) => !in_array($key, $except, true),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
