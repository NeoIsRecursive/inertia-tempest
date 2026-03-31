<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline\Stages;

use Closure;
use NeoIsRecursive\Inertia\Contracts\CallableProp;
use NeoIsRecursive\Inertia\Pipeline\PropPipelineContext;
use NeoIsRecursive\Inertia\Pipeline\PropStage;
use Tempest\Support\Arr\ArrayInterface;

use function Tempest\Container\invoke;
use function Tempest\Support\arr;

final readonly class EvaluateProps implements PropStage
{
    public function __invoke(PropPipelineContext $context): PropPipelineContext
    {
        return $context->with(['evaluatedProps' => $this->evaluate($context->renderableProps)]);
    }

    /**
     * @param array<mixed> $props
     * @return array<mixed>
     * @mago-expect lint:no-boolean-flag-parameter
     */
    private function evaluate(array $props, bool $unpackDotProps = true): array
    {
        return arr($props)->map(function (mixed $value, string|int $key): array {
            $evaluated = $value
                |> (static fn(mixed $value) => $value instanceof Closure ? invoke($value) : $value)
                |> (static fn(mixed $value) => $value instanceof CallableProp ? $value() : $value)
                |> (static fn(mixed $value) => $value instanceof ArrayInterface ? $value->toArray() : $value)
                |> (fn(mixed $value) => is_array($value) ? $this->evaluate($value, unpackDotProps: false) : $value);

            return [$key, $evaluated];
        })->reduce(
            static function (array $acc, array $item) use ($unpackDotProps): array {
                /** @var string|int $key */
                [$key, $value] = $item;

                if ($unpackDotProps && is_string($key) && str_contains($key, '.')) {
                    return arr($acc)->set($key, $value)->toArray();
                }

                $acc[$key] = $value;

                return $acc;
            },
            [],
        );
    }
}
