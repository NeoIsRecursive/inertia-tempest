<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Contracts;

interface InertiaVersionResolver
{
    public function resolve(): string;
}
