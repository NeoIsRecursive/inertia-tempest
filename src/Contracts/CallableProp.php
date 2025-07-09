<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Contracts;

interface CallableProp
{
    public function __invoke(): mixed;
}
