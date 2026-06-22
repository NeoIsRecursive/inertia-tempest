<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia {
    use NeoIsRecursive\Inertia\Http\Component;

    use function Tempest\Container\get;

    /**
     * @param string|null $component
     * @param array<string, mixed> $props
     * @return ($component is null ? Inertia : Component)
     */
    function inertia(?string $component = null, array $props = []): Component|Inertia
    {
        if ($component === null) {
            return get(Inertia::class);
        }

        return get(Inertia::class)->render($component, $props);
    }
}
