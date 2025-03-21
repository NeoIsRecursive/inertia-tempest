<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Support;

final readonly class Header
{
    public const string INERTIA = 'x-inertia';
    public const string ERROR_BAG = 'x-inertia-error-bag';
    public const string LOCATION = 'x-inertia-location';
    public const string VERSION = 'x-inertia-version';
    public const string PARTIAL_COMPONENT = 'x-inertia-partial-component';
    public const string PARTIAL_ONLY = 'x-inertia-partial-data';
    public const string PARTIAL_EXCEPT = 'x-inertia-partial-except';
    public const string RESET = 'X-Inertia-Reset';
}
