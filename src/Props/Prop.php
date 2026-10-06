<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

interface Prop
{
    public function shouldBeEvaluated(PropContext $context): bool;

    public function getMeta(PropContext $context, mixed $resolvedValue): array;

    public function resolveValue(PropContext $context): mixed;

    public function serialize(mixed $resolvedValue): mixed;
}
