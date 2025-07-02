<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Http;

use Closure;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\DeferProp;
use NeoIsRecursive\Inertia\Props\LazyProp;
use NeoIsRecursive\Inertia\Support\Header;
use NeoIsRecursive\Inertia\Views\InertiaBaseView;
use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\invoke;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class InertiaResponse implements Response
{
    use IsResponse;

    public function __construct(
        readonly Request $request,
        readonly string $page,
        readonly array $props,
        readonly string $rootView,
        readonly string $version,
    ) {
        $deferredProps = self::resolvePropKeysThatShouldDefer(
            props: $props,
            request: $request,
            component: $page,
        );

        $mergeProps = self::resolvePropKeysThatShouldMerge(
            props: $props,
            request: $request,
        );

        // Build page data immutably
        $pageData = array_merge(
            [
                'component' => $page,
                'props' => self::composeProps(
                    props: $this->props,
                    request: $this->request,
                    component: $page,
                ),
                'url' => $request->uri,
                'version' => $version,
            ],
            count($deferredProps) ? ['deferredProps' => $deferredProps] : [],
            count($mergeProps) ? ['mergeProps' => $mergeProps] : [],
        );

        $isInertia = $request->headers->has(Header::INERTIA);

        $this->body = $isInertia
            ? (function () use ($pageData) {
                // side effect to set Inertia header
                $this->addHeader(Header::INERTIA, value: 'true');

                return $pageData;
            })()
            : new InertiaBaseView(
                view: $rootView,
                pageData: $pageData,
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

    private static function resolvePropKeysThatShouldDefer(array $props, Request $request, string $component): array
    {
        if (static::isPartial($request, $component)) {
            return [];
        }

        return arr($props)
            ->filter(function ($prop) {
                return $prop instanceof DeferProp;
            })
            ->map(fn(DeferProp $prop, string $key) => [
                'group' => $prop->group,
                'key' => $key,
            ])
            ->groupBy(fn(array $prop) => $prop['group'])
            ->map(fn(array $group) => arr($group)->pluck(value: 'key')->toArray())
            ->toArray();
    }

    private static function resolvePropKeysThatShouldMerge(array $props, Request $request): array
    {
        $resetProps = arr(explode(
            separator: ',',
            string: $request->headers->get(Header::RESET) ?? '',
        ));
        return arr($props)
            ->filter(fn($prop) => $prop instanceof MergeableProp && $prop->shouldMerge)
            ->filter(fn($_, $key) => !$resetProps->contains($key))
            ->keys()
            ->toArray();
    }

    /**
     * @pure
     * Evaluates props recursively.
     */
    private static function evaluateProps(array $props, Request $request, bool $unpackDotProps = true): array // @mago-expect best-practices/no-boolean-flag-parameter
    {
        return arr($props)
            ->map(function ($value, string|int $key) use ($request): array {
                $evaluated = ($value instanceof Closure) ? invoke($value) : $value;
                $evaluated =
                    $evaluated instanceof LazyProp || $evaluated instanceof AlwaysProp ? $evaluated() : $evaluated;
                $evaluated = ($evaluated instanceof ImmutableArray) ? $evaluated->toArray() : $evaluated;
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
