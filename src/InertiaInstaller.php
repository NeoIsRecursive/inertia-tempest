<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia;

use RuntimeException;
use Tempest\Console\Console;
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;

use function Tempest\root_path;

final class InertiaInstaller implements Installer
{
    use PublishesFiles;

    public function __construct(private InertiaConfig $inertiaConfig, private readonly Console $console) {}

    public function getName(): string
    {
        return 'inertia';
    }

    public function install(): void
    {

        $framework = $this->ask(
            question: 'What frontend framework will you use?',
            default: true,
            options: ['react'],
            asList: true,
        );

        match ($framework) {
            'react' => $this->installReact(),
            default => throw new RuntimeException('Invalid framework'),
        };
    }

    private function installReact()
    {
        $jsLocation = $this->ask(
            question: 'Where do you want to put your assets?',
            default: "resources",
        );

        $this->publish(
            source: __DIR__ . '/../stubs/app.view.php',
            destination: $this->inertiaConfig->rootView,
        );

        $this->publish(
            source: __DIR__ . '/../stubs/react/main.tsx',
            destination: root_path($jsLocation, '/main.tsx'),
        );

        $this->publish(
            source: __DIR__ . '/../stubs/react/tsconfig.json',
            destination: root_path('tsconfig.json'),
        );

        $this->publish(
            source: __DIR__ . '/../stubs/react/welcome.tsx',
            destination: root_path($jsLocation, '/pages/welcome.tsx'),
        );

        $this->confirm(
            question: 'Do you want to install React dependencies?',
            default: true,
        ) && passthru('npm install react react-dom @inertiajs/react && npm install -D typescript @types/react @types/react-dom @types/node');

        // TODO: Add scripts to package.json
        // $shouldAddNodeScripts = $this->confirm(
        //     question: 'Do you want to add scripts to your package.json?',
        //     default: true,
        // );

        // if ($shouldAddNodeScripts) {
        //     $packageJson = json_decode(file_get_contents(root_path('package.json')), associative: true);

        //     $packageJson['scripts']['dev'] = 'tsc -w --outDir public/dist';
        //     $packageJson['scripts']['build'] = 'tsc --outDir public/dist';

        //     file_put_contents(root_path('package.json'), json_encode($packageJson, JSON_PRETTY_PRINT));
        // }
    }
}
