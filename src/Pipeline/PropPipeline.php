<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline;

use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\NotResolvedValue;
use NeoIsRecursive\Inertia\Props\Prop;
use NeoIsRecursive\Inertia\Props\PropContext;
use NeoIsRecursive\Inertia\Props\RegularProp;
use Tempest\Container\Container;
use Tempest\Http\Request;
use Tempest\Support\Arr\ArrayInterface;

use function Tempest\Support\arr;
use function Tempest\Support\Arr\get_by_key;
use function Tempest\Support\Arr\has_key;
use function Tempest\Support\Arr\is_associative;
use function Tempest\Support\Arr\is_list;
use function Tempest\Support\Arr\set_by_key;

final readonly class PropPipeline
{
    /**
     * @param array<string,Prop> $props
     */
    public function process(
        array $props,
        Container $container,
        Request $request,
        string $component,
        bool $unpackDotProps = true,
    ): ProcessedProps {
        $meta = [];
        $evaluatedProps = [];

        $appendToPropList = function (string $key, mixed $value) use ($unpackDotProps, &$evaluatedProps) {
            if ($unpackDotProps && str_contains($key, '.')) {
                if (has_key($evaluatedProps, $key)) {
                    $value = array_merge($value, get_by_key($evaluatedProps, $key) ?? []);
                }

                $evaluatedProps = set_by_key([...$evaluatedProps], $key, $value);

                return;
            }

            $evaluatedProps[$key] = $value;
        };

        arr($props)->each(function (mixed $value, string $key) use (
            $container,
            $request,
            $component,
            $unpackDotProps,
            &$meta,
            $appendToPropList,
        ) {
            $prop = match (true) {
                $value instanceof Prop => $value,
                $value instanceof ArrayInterface => $this->process(
                    $value->toArray(),
                    $container,
                    $request,
                    $component,
                    false,
                ),
                is_callable($value) => new RegularProp($value),
                is_array($value) && !array_is_list($value) => $this->process(
                    $value,
                    $container,
                    $request,
                    $component,
                    unpackDotProps: false,
                ),
                default => $value,
            };

            if (!$value instanceof Prop) {
                $appendToPropList($key, $value);

                return;
            }

            if ($prop instanceof ProcessedProps) {
                $meta = array_merge_recursive($meta, $prop->meta);
                $appendToPropList($key, $prop->props);

                return;
            }

            if (!$prop->shouldBeEvaluated(new PropContext($container, $component, (string) $key, $request))) {
                $meta = array_merge_recursive($meta, $prop->getMeta(
                    new PropContext($container, $component, (string) $key, $request),
                    new NotResolvedValue(),
                ));
                return;
            }

            $context = new PropContext($container, $component, (string) $key, $request);
            $value = $prop->resolveValue($context);

            $meta = array_merge_recursive($meta, $prop->getMeta($context, $value));

            $appendToPropList($key, $prop->serialize($value));
        });

        return new ProcessedProps(props: $evaluatedProps, meta: $meta);
    }
}
