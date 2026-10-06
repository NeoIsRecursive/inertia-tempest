<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use Closure;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use Override;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;
use Tempest\Support\Arr\ArrayInterface;

final class OptionalProp implements Prop, MergeableProp
{
    use IsMergeable;

    public function __construct(
        public MethodReflector|FunctionReflector|Closure $callback,
    ) {}

    #[\Override]
    public function getMeta(PropContext $context, mixed $resolvedValue): array
    {
        return $this->resolveMergeMetadata($context);
    }

    #[\Override]
    public function shouldBeEvaluated(PropContext $context): bool
    {
        if (!$context->isPartial) {
            return false;
        }

        if (in_array($context->key, $context->except, strict: true)) {
            return false;
        }

        return in_array($context->key, $context->only, strict: true);
    }

    #[\Override]
    public function resolveValue(PropContext $context): mixed
    {
        return $context->container->invoke($this->callback);
    }

    #[\Override]
    public function serialize(mixed $resolvedValue): mixed
    {
        if ($resolvedValue instanceof ArrayInterface) {
            return $resolvedValue->toArray();
        }

        return $resolvedValue;
    }
}
