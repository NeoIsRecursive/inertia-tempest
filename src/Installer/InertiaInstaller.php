<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Installer;

use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Support\JavaScript\DependencyInstaller;

use function Tempest\root_path;
use function Tempest\src_path;
use function Tempest\Support\Filesystem\read_file;
use function Tempest\Support\Filesystem\write_file;
use function Tempest\Support\path;
use function Tempest\Support\Path\to_absolute_path;
use function Tempest\Support\Path\to_relative_path;
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
            ],
            default: 'react'
        );

        $clientPath = $this->ask(
            question: 'Where is your client-side code located?',
            default: to_relative_path(root_path(), src_path('Client')),
        );

        $pagesPath = $this->ask(
            question: 'Where do you want to keep your Inertia pages?',
            default: to_relative_path(to_absolute_path($clientPath), to_absolute_path($clientPath, 'pages')),
        );

        $this->publish(
            source: __DIR__ . '/app.view.stub',
            destination: (string) path(
                src_path(),
                'app.view.php'
            ),
        );

        match ($framework) {
            'react' => $this->installReact($clientPath, $pagesPath),
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
            ],
        );

        $this->publish(
            source: __DIR__ . '/React/main.tsx',
            destination: (string) path(
                $clientPath,
                'main.entrypoint.tsx'
            ),
            callback: function (string $source, string $target) use ($pagesPath): void {
                $content = read_file($target);

                write_file($target, replace(
                    string: $content,
                    search: '{{page_directory}}',
                    replace: "./{$pagesPath}"
                ));
            }
        );

        $this->publish(
            source: __DIR__ . '/React/example-page.tsx',
            destination: (string) path(
                $clientPath,
                $pagesPath,
                'example-page.tsx'
            )
        );

        $this->publishImports();
    }
}
