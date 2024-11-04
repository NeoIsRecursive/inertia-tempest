<?php

namespace NeoIsRecursive\Inertia\Http;

use Closure;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\LazyProp;
use NeoIsRecursive\Inertia\Support\Header;
use NeoIsRecursive\Inertia\Views\InertiaBaseView;
use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;

use function Tempest\invoke;
use function Tempest\Support\arr;

final class InertiaResponse implements Response
{
    use IsResponse;

    public function __construct(
        Request $request,
        string $page,
        array $component,
        string $rootView,
        string $version,
    ) {
        $alwaysProps = $this->resolveAlwaysProps(props: $component);
        $component = $this->resolvePartialProps(request: $request, component: $page, props: $component);

        $component = $this->evaluateProps(
            props: array_merge($component, $alwaysProps),
            request: $request,
            unpackDotProps: true
        );

        $page = [
            'component' => $page,
            'props' => $component,
            'url' => $request->getUri(),
            'version' => $version,
        ];


        if (array_key_exists(Header::INERTIA, $request->getHeaders()) && $request->getHeaders()[Header::INERTIA] == 'true') {
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
        $headers = $request->getHeaders();

        $partialHeader = $headers[Header::PARTIAL_COMPONENT] ?? null;

        $isPartial = $partialHeader === $component;

        if (! $isPartial) {
            return array_filter($props, static function ($prop) {
                return ! ($prop instanceof LazyProp);
            });
        }

        $only = array_filter(explode(',', $headers[Header::PARTIAL_ONLY] ?? ''));
        $except = array_filter(explode(',', $headers[Header::PARTIAL_EXCEPT] ?? ''));

        $props = $only ? array_intersect_key($props, array_flip((array) $only)) : $props;

        if ($except) {
            foreach ($except as $key) {
                unset($props[$key]);
            }
        }

        return $props;
    }

    public function evaluateProps(array $props, Request $request, bool $unpackDotProps = true): array
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

            if (is_array($value)) {
                $value = $this->evaluateProps($value, $request, false);
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
