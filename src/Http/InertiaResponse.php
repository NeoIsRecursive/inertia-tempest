<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Http;

use Closure;
use NeoIsRecursive\Inertia\Contracts\CallableProp;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use NeoIsRecursive\Inertia\PageData;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\DeferProp;
use NeoIsRecursive\Inertia\Props\LazyProp;
use NeoIsRecursive\Inertia\Support\Header;
use NeoIsRecursive\Inertia\Views\InertiaBaseView;
use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Support\Arr\ArrayInterface;

use function Tempest\invoke;
use function Tempest\Support\arr;

final class InertiaResponse implements Response
{
    use IsResponse;

    // @mago-expect maintainability/excessive-parameter-list
    public function __construct(
        readonly Request $request,
        readonly string $component,
        readonly array $props,
        readonly string $rootView,
        readonly string $version,
        readonly bool $clearHistory = false,
        readonly bool $encryptHistory = false,
    ) {
        $pageData = new PageData(
            component: $component,
            props: self::composeProps(
                props: $props,
                request: $request,
                component: $component,
            ),
            url: $request->uri,
            version: $version,
            clearHistory: $clearHistory,
            encryptHistory: $encryptHistory,
            propKeysToDefer: self::resolvePropKeysThatShouldDefer(
                props: $props,
                request: $request,
                component: $component,
            ),
            propsKeysToMerge: self::resolvePropKeysThatShouldMerge(
                props: $props,
                request: $request,
            ),
        );

        $this->body = $request->headers->has(Header::INERTIA)
            ? (function () use ($pageData) {
                // side effect to set Inertia header
                $this->addHeader(Header::INERTIA, value: 'true');

                return $pageData->toArray();
            })()
            : new InertiaBaseView(
                path: $rootView,
                page: $pageData,
            );
    }

    public static function isPartial(Request $request, string $component): bool
    {
        return $request->headers->get(Header::PARTIAL_COMPONENT) === $component;
    }

    /**
     * @pure
     * Composes the various prop transformations into one functional pipeline.
     */
    private static function composeProps(array $props, Request $request, string $component): array
    {
        $always = static::resolveAlwaysProps($props);
        $partial = static::resolvePartialProps($request, $component, $props);

        return static::evaluateProps(array_merge($always, $partial), $request, unpackDotProps: true);
    }

    /**
     * @pure
     * function to extract AlwaysProp instances.
     */
    private static function resolveAlwaysProps(array $props): array
    {
        return array_filter($props, static fn($prop) => $prop instanceof AlwaysProp);
    }

    /**
     * @pure
     * function to extract Partial props based on request headers.
     */
    private static function resolvePartialProps(Request $request, string $component, array $props): array
    {
        $headers = $request->headers;

        if (!static::isPartial($request, $component)) {
            return array_filter($props, static fn($prop) => !($prop instanceof LazyProp || $prop instanceof DeferProp));
        }

        $only = array_filter(explode(
            separator: ',',
            string: $headers->get(Header::PARTIAL_ONLY) ?? '',
        ));
        $except = array_filter(explode(
            separator: ',',
            string: $headers->get(Header::PARTIAL_EXCEPT) ?? '',
        ));

        $filtered = $only ? array_intersect_key($props, array_flip($only)) : $props;

        return array_filter($filtered, static fn($key) => !in_array($key, $except, strict: true), ARRAY_FILTER_USE_KEY);
    }

    private static function resolvePropKeysThatShouldDefer(array $props, Request $request, string $component): ?array
    {
        if (static::isPartial($request, $component)) {
            return null;
        }

        $propKeysToMerge = arr($props)
            ->filter(static fn($prop) => $prop instanceof DeferProp)
            ->map(static fn(DeferProp $prop, string $key) => [
                'group' => $prop->group,
                'key' => $key,
            ])
            ->groupBy(static fn(array $prop) => $prop['group'])
            ->map(static fn(array $group) => arr($group)->pluck(value: 'key')->toArray());

        return $propKeysToMerge->isEmpty() ? null : $propKeysToMerge->toArray();
    }

    private static function resolvePropKeysThatShouldMerge(array $props, Request $request): ?array
    {
        $resetProps = arr(explode(
            separator: ',',
            string: $request->headers->get(Header::RESET) ?? '',
        ));

        $propKeysToMerge = arr($props)
            ->filter(fn($prop) => $prop instanceof MergeableProp && $prop->shouldMerge)
            ->filter(fn($_, $key) => !$resetProps->contains($key))
            ->keys();

        return $propKeysToMerge->isEmpty() ? null : $propKeysToMerge->toArray();
    }

    /**
     * Evaluates props recursively.
     * @pure
     */
    private static function evaluateProps(array $props, Request $request, bool $unpackDotProps = true): array // @mago-expect best-practices/no-boolean-flag-parameter
    {
        return arr($props)
            ->map(function ($value, string|int $key) use ($request): array {
                $evaluated = ($value instanceof Closure) ? invoke($value) : $value;
                $evaluated = ($evaluated instanceof CallableProp) ? $evaluated() : $evaluated;
                $evaluated = ($evaluated instanceof ArrayInterface) ? $evaluated->toArray() : $evaluated;
                $evaluated = is_array($evaluated)
                    ? self::evaluateProps($evaluated, $request, unpackDotProps: false)
                    : $evaluated;

                return [$key, $evaluated];
            })
            ->reduce(function (array $acc, array $item) use ($unpackDotProps): array {
                [$key, $value] = $item;
                if ($unpackDotProps && is_string($key) && str_contains($key, needle: '.')) {
                    return arr($acc)->set($key, $value)->toArray();
                }
                $acc[$key] = $value;
                return $acc;
            }, []);
    }
}
