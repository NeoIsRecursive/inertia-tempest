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
            expected: '<div id="app" data-page="{&quot;component&quot;:&quot;TestComponent&quot;,&quot;props&quot;:{&quot;key&quot;:&quot;value&quot;},&quot;url&quot;:&quot;/test-url&quot;,&quot;version&quot;:&quot;1.0.0&quot;,&quot;clearHistory&quot;:false,&quot;encryptHistory&quot;:false}"></div>',
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
            expected: '<div id="app" data-page="{&quot;component&quot;:&quot;TestComponent&quot;,&quot;props&quot;:{&quot;key&quot;:&quot;value&quot;},&quot;url&quot;:&quot;/test-url&quot;,&quot;version&quot;:&quot;1.0.0&quot;,&quot;clearHistory&quot;:false,&quot;encryptHistory&quot;:false}"></div>',
            actual: $output,
        );
    }

    public function test_renders_id_attribute_correctly(): void
    {
        $output = $this->render(<<<HTML
            <x-inertia id="custom-id" :page="new \NeoIsRecursive\Inertia\PageData('TestComponent', ['key' => 'value'], '/test-url', '1.0.0', false, false)" />
        HTML);

        static::assertSnippetsMatch(
            expected: '<div id="custom-id" data-page="{&quot;component&quot;:&quot;TestComponent&quot;,&quot;props&quot;:{&quot;key&quot;:&quot;value&quot;},&quot;url&quot;:&quot;/test-url&quot;,&quot;version&quot;:&quot;1.0.0&quot;,&quot;clearHistory&quot;:false,&quot;encryptHistory&quot;:false}"></div>',
            actual: $output,
        );
    }
}
