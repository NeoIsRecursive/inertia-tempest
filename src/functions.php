<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia {

    use NeoIsRecursive\Inertia\Inertia;
    use NeoIsRecursive\Inertia\Http\InertiaResponse;

    use function Tempest\get;

    function inertia(string $page, array $props = []): InertiaResponse
    {
        return get(Inertia::class)->render($page, $props);
    }
}
