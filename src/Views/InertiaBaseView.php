<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Views;

use NeoIsRecursive\Inertia\PageData;
use Tempest\View\IsView;
use Tempest\View\View;

final class InertiaBaseView implements View
{
    use IsView;

    public function __construct(
        public string $path,
        public PageData $page,
    ) {}
}
