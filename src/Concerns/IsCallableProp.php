<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Concerns;

use function Tempest\Container\invoke;

trait IsCallableProp
{
    /**
     * Call the given value if callable and inject its dependencies.
     */
    public function resolveCallablePropValue(mixed $value): mixed
    {
        return $this->useAsCallable($value) ? invoke($value) : $value;
    }

    /**
     * Determine if the given value is callable, but not a string.
     */
    protected function useAsCallable(mixed $value): bool
    {
        return !is_string($value) && is_callable($value);
    }
}
