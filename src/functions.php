<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia {
    use NeoIsRecursive\Inertia\Http\InertiaResponse;
    use NeoIsRecursive\Inertia\Inertia;

    use function Tempest\get;

    /**
     * @return ($component is null ? Inertia : InertiaResponse)
     */
    function inertia(string|null $component = null, array $props = []): InertiaResponse|Inertia
    {
        if ($component === null) {
            return get(Inertia::class);
        }

        return get(Inertia::class)->render($component, $props);
    }
}
