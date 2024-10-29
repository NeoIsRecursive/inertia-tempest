<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Contracts;

interface SharedPropsResolver
{
    public function resolve(): array;
}
