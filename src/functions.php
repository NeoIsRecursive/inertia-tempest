<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia {

    use NeoIsRecursive\Inertia\Inertia;
    use NeoIsRecursive\Inertia\Http\InertiaResponse;

    use function Tempest\get;

    /**
     * @return ($page is null ? Inertia : InertiaResponse)
     */
    function inertia(?string $page = null, array $props = []): InertiaResponse|Inertia
    {
        if ($page === null) {
            return get(Inertia::class);
        }

        return get(Inertia::class)->render($page, $props);
    }
}
