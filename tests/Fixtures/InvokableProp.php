<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Fixtures;

final class InvokableProp
{
    public function __invoke(): string
    {
        return 'invokable';
    }
}
