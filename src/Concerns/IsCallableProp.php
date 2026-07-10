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
        return !is_string($value) && is_callable($value) ? invoke($value) : $value;
    }
}
