<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

interface CallableProp
{
    public function __invoke(): mixed;
}
