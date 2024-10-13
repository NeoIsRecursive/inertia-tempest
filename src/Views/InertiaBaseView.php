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

    public function reactRefresh(): string
    {
        return <<<'html'
        <script type="module">
            import RefreshRuntime from 'http://localhost:5173/@react-refresh'
            RefreshRuntime.injectIntoGlobalHook(window)
            window.$RefreshReg$ = () => {}
            window.$RefreshSig$ = () => (type) => type
            window.__vite_plugin_react_preamble_installed__ = true
        </script>
        html;
    }
}
