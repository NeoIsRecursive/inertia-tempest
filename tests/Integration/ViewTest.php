<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Tests\TestCase;
use NeoIsRecursive\Inertia\Views\InertiaBaseView;

final class ViewTest extends TestCase
{
    public function test_inertia_component_renders_correctly(): void
    {
        $output = $this->render(<<<HTML
            <x-inertia :page="new \NeoIsRecursive\Inertia\PageData('TestComponent', ['key' => 'value'], '/test-url', '1.0.0', false, false)" id="app" />
        HTML);

        static::assertSnippetsMatch(
            expected: <<<HTML
            <script data-page="app" type="application/json">
            {
                "component": "TestComponent",
                "props":{
                    "key": "value"
                },
                "url": "/test-url",
                "version": "1.0.0",
                "clearHistory": false,
                "encryptHistory": false
            }
            </script>
            <div id="app"></div>
            HTML,
            actual: $output,
        );
    }

    public function test_inertia_page_can_render_compnent(): void
    {
        $output = $this->render(
            new InertiaBaseView(
                <<<HTML
                    <x-inertia />
                HTML,
                new \NeoIsRecursive\Inertia\PageData(
                    component: 'TestComponent',
                    props: ['key' => 'value'],
                    url: '/test-url',
                    version: '1.0.0',
                    clearHistory: false,
                    encryptHistory: false,
                ),
            ),
        );

        static::assertSnippetsMatch(
            expected: <<<HTML
                <script data-page="app" type="application/json">
                {
                  "component": "TestComponent",
                  "props": {
                    "key": "value"
                  },
                  "url": "/test-url",
                  "version": "1.0.0",
                  "clearHistory": false,
                  "encryptHistory": false
                }
                </script>
                <div id="app"></div>
            HTML,
            actual: $output,
        );
    }

    public function test_renders_id_attribute_correctly(): void
    {
        $output = $this->render(<<<HTML
            <x-inertia id="custom-id" :page="new \NeoIsRecursive\Inertia\PageData('TestComponent', ['key' => 'value'], '/test-url', '1.0.0', false, false)" />
        HTML);

        static::assertSnippetsMatch(
            expected: <<<'html'
                <script data-page="custom-id" type="application/json">
                    {
                        "component":"TestComponent",
                        "props": {
                            "key":"value"
                        },
                        "url":"/test-url",
                        "version":"1.0.0",
                        "clearHistory":false,
                        "encryptHistory":false
                    }
                </script>
                <div id="custom-id"></div>
            html,
            actual: $output,
        );
    }
}
