<?php

namespace NeoIsRecursive\Inertia\Http;

use Closure;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\LazyProp;
use NeoIsRecursive\Inertia\Support\Header;
use NeoIsRecursive\Inertia\Views\InertiaBaseView;
use Tempest\Http\Status;
use Tempest\Router\IsResponse;
use Tempest\Router\Request;
use Tempest\Router\Response;
use Tempest\Support\ArrayHelper;

use function Tempest\invoke;
use function Tempest\Support\arr;

final class InertiaResponse implements Response
{
    use IsResponse;

    public function __construct(
        Request $request,
        string $page,
        array $props,
        string $rootView,
        string $version,
    ) {

        $alwaysProps = $this->resolveAlwaysProps(props: $props);
        $partialProps = $this->resolvePartialProps(request: $request, component: $page, props: $props);
        $mergeProps = $this->resolveMergeProps($props, $request);

        $props = $this->evaluateProps(
            props: array_merge(
                $alwaysProps,
                $partialProps,
                $mergeProps
            ),
            request: $request,
            unpackDotProps: true
        );

        $page = [
            'component' => $page,
            'props' => $props,
            'url' => $request->uri,
            'version' => $version,
            'mergeProps' => array_keys($mergeProps),
        ];


        if (array_key_exists(Header::INERTIA, $request->headers) && $request->headers[Header::INERTIA] == 'true') {
            $this->status = Status::OK;

            $this->body = $page;

            $this->addHeader(Header::INERTIA, 'true');
            return;
        }

        $this->body = new InertiaBaseView(
            view: $rootView,
            pageData: $page,
        );
    }

    private function resolveAlwaysProps(array $props): array
    {
        $always = array_filter($props, static function ($prop) {
            return $prop instanceof AlwaysProp;
        });

        return $always;
    }

    private function resolvePartialProps(Request $request, string $component, array $props): array
    {
        $headers = $request->headers;

        $partialHeader = $headers[Header::PARTIAL_COMPONENT] ?? null;

        $isPartial = $partialHeader === $component;

        if (! $isPartial) {
            return array_filter($props, static function ($prop) {
                return ! ($prop instanceof LazyProp);
            });
        }

        $only = array_filter(explode(',', $headers[Header::PARTIAL_ONLY] ?? ''));
        $except = array_filter(explode(',', $headers[Header::PARTIAL_EXCEPT] ?? ''));

        $props = $only ? array_intersect_key($props, array_flip($only)) : $props;

        if (count($except) > 0) {
            foreach ($except as $key) {
                unset($props[$key]);
            }
        }

        return $props;
    }

    public static function resolveMergeProps(array $props, Request $request): array
    {
        $resetProps = arr(explode(',', $request->headers[Header::RESET] ?? ''));
        $mergeProps = arr($props)
            ->filter(fn($prop) => $prop instanceof MergeableProp && $prop->shouldMerge)
            ->filter(
                fn($_, $key) => ! $resetProps->contains($key)
            )
            ->keys();

        return $mergeProps->toArray();
    }

    public static function evaluateProps(array $props, Request $request, bool $unpackDotProps = true): array
    {
        foreach ($props as $key => $value) {
            if ($value instanceof Closure) {
                $value = invoke($value);
            }

            if ($value instanceof LazyProp) {
                $value = $value();
            }

            if ($value instanceof AlwaysProp) {
                $value = $value();
            }

            if ($value instanceof ArrayHelper) {
                $value = $value->toArray();
            }

            if (is_array($value)) {
                $value = self::evaluateProps($value, $request, false);
            }

            if ($unpackDotProps && str_contains($key, '.')) {
                $props = arr($props)->set($key, $value)->toArray();
                unset($props[$key]);
            } else {
                $props[$key] = $value;
            }
        }

        return $props;
    }
}
