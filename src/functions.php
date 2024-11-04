<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia {

    use NeoIsRecursive\Inertia\Inertia;
    use NeoIsRecursive\Inertia\Http\InertiaResponse;

    use function Tempest\get;

    /**
     * @return ($component is null ? Inertia : InertiaResponse)
     */
    function inertia(?string $component = null, array $props = []): InertiaResponse|Inertia
    {
        if ($component === null) {
            return get(Inertia::class);
        }

        return get(Inertia::class)->render($component, $props);
    }
}
