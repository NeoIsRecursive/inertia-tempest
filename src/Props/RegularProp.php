<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use Tempest\Support\Arr\ArrayInterface;

final readonly class RegularProp implements Prop
{
    public function __construct(
        private mixed $value,
    ) {}

    #[\Override]
    public function getMeta(PropContext $context, mixed $resolvedValue): array
    {
        return [];
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

    #[\Override]
    public function shouldBeEvaluated(PropContext $context): bool
    {
        if (!$context->isPartial) {
            return true;
        }

        if (in_array($context->key, $context->except, strict: true)) {
            return false;
        }

        return in_array($context->key, $context->only, strict: true);
    }
}
