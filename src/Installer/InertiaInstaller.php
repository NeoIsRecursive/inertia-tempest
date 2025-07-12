<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Installer;

use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Highlight\Languages\JavaScript\JavaScriptLanguage;
use Tempest\Support\JavaScript\DependencyInstaller;

use function Tempest\root_path;
use function Tempest\src_path;
use function Tempest\Support\Filesystem\read_file;
use function Tempest\Support\Filesystem\write_file;
use function Tempest\Support\path;
use function Tempest\Support\Path\to_absolute_path;
use function Tempest\Support\Path\to_relative_path;
use function Tempest\Support\Str\ensure_starts_with;
use function Tempest\Support\Str\replace;

final class InertiaInstaller implements Installer
{
    use PublishesFiles;

    private(set) string $name = 'inertia';

    public function __construct(
        private readonly DependencyInstaller $javascript,
    ) {}

    public function install(): void
    {
        $framework = $this->ask(
            question: 'What frontend framework are you going to use?',
            options: [
                'react' => 'React',
                'vue' => 'Vue',
            ],
            default: 'react',
        );

        $clientPath = $this->ask(
            question: 'Where is your client-side code located?',
            // @mago-expect best-practices/literal-named-argument
            default: to_relative_path(root_path(), src_path('Client')),
        );

        $pagesPath = $this->ask(
            question: 'Where do you want to keep your Inertia pages?',
            // @mago-expect best-practices/literal-named-argument
            default: to_relative_path(to_absolute_path($clientPath), to_absolute_path($clientPath, 'pages')),
        );

        $this->publish(
            source: __DIR__ . '/app.view.stub',
            // @mago-expect best-practices/literal-named-argument
            destination: (string) path(src_path(), 'app.view.php'),
        );

        match ($framework) {
            'react' => $this->installReact($clientPath, $pagesPath),
            'vue' => $this->installVue($clientPath, $pagesPath),
        };
    }

    private function installReact(string $clientPath, string $pagesPath): void
    {
        $this->javascript->installDependencies(
            cwd: root_path(),
            dependencies: [
                '@inertiajs/react',
                'react',
                'react-dom',
                '@vitejs/plugin-react',
                '@types/react',
                '@types/react-dom',
            ],
        );

        $this->publish(
            source: __DIR__ . '/React/main.tsx',
            // @mago-expect best-practices/literal-named-argument
            destination: (string) path($clientPath, 'main.entrypoint.tsx'),
            callback: function (string $_, string $target) use ($pagesPath): void {
                $content = read_file($target);

                write_file($target, replace(
                    string: $content,
                    search: '{{page_directory}}',
                    replace: ensure_starts_with($pagesPath, prefix: './'),
                ));
            },
        );

        $this->publish(
            source: __DIR__ . '/React/example-page.tsx',
            // @mago-expect best-practices/literal-named-argument
            destination: (string) path($clientPath, $pagesPath, 'example-page.tsx'),
        );

        $this->console->instructions(lines: [
            'To make vite bundle jsx and enable fast refresh, you need the @vitejs/plugin-react plugin.',
            'We have installed it for you, but you need to add it to your Vite config:',
        ]);

        $this->console->writeWithLanguage(
            contents: <<<'JS'
                import react from '@vitejs/plugin-react';
                // ...
                export default defineConfig({
                    plugins: [
                        // ... other plugins
                        react(),
                    ],
                });
            JS,
            language: new JavaScriptLanguage(),
        );
    }

    private function installVue(string $clientPath, string $pagesPath): void
    {
        $this->javascript->installDependencies(
            cwd: root_path(),
            dependencies: [
                '@inertiajs/vue3',
                'vue',
                '@vitejs/plugin-vue',
            ],
        );

        $this->publish(
            source: __DIR__ . '/Vue/main.ts',
            // @mago-expect best-practices/literal-named-argument
            destination: (string) path($clientPath, 'main.entrypoint.ts'),
            callback: function (string $_, string $target) use ($pagesPath): void {
                $content = read_file($target);

                write_file($target, replace(
                    string: $content,
                    search: '{{page_directory}}',
                    replace: ensure_starts_with($pagesPath, prefix: './'),
                ));
            },
        );

        $this->publish(
            source: __DIR__ . '/Vue/example-page.vue',
            // @mago-expect best-practices/literal-named-argument
            destination: (string) path($clientPath, $pagesPath, 'example-page.vue'),
        );

        $this->console->instructions(lines: [
            'Vite requires a plugin to parse Vue files',
            'we have installed it for you, but you need to add it to your vite config:',
        ]);

        $this->console->writeWithLanguage(
            contents: <<<'js'
                import vue from '@vitejs/plugin-vue';
                // ...
                export default defineConfig({
                    plugins: [
                        // ... other plugins
                        vue(),
                    ],
                });
            js,
            language: new JavaScriptLanguage(),
        );
    }
}
