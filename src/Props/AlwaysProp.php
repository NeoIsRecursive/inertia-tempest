<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use Override;
use Tempest\Support\Arr\ArrayInterface;

final class AlwaysProp implements Prop, MergeableProp
{
    use IsMergeable;

    public function __construct(
        public readonly mixed $value,
    ) {}

    #[\Override]
    public function getMeta(PropContext $context, mixed $resolvedValue): array
    {
        return $this->resolveMergeMetadata($context);
    }

    #[\Override]
    public function shouldBeEvaluated(PropContext $context): bool
    {
        return true;
    }

    #[\Override]
    public function resolveValue(PropContext $context): mixed
    {
        return !is_string($this->value) && is_callable($this->value)
            ? $context->container->invoke($this->value)
            : $this->value;
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
