<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use Tempest\Support\Arr\ArrayInterface;

final class DeferProp implements Prop, MergeableProp
{
    use IsMergeable;

    public function __construct(
        public readonly mixed $value,
        public readonly string $group = 'default',
    ) {}

    #[\Override]
    public function getMeta(PropContext $context, mixed $resolvedValue): array
    {
        if ($resolvedValue instanceof NotResolvedValue) {
            return [
                'propKeysToDefer' => [
                    $this->group => [$context->key],
                ],
            ];
        }
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
