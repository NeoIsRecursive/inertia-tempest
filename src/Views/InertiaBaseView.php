<?php

namespace NeoIsRecursive\Inertia\Views;

use Tempest\View\IsView;
use Tempest\View\View;

final class InertiaBaseView implements View
{
    use IsView;

    public function __construct(
        private string $view,
        private array $pageData,
    ) {
        $this->path = $view;
    }

    public function renderInertiaElement(string $id): string
    {
        $pageData = htmlentities((json_encode($this->pageData)));

        $template = <<<HTML
             <div id="{$id}" data-page="{$pageData}"></div>
        HTML;

        return $template;
    }
}
