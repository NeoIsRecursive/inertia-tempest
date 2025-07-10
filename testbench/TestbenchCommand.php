<?php

declare(strict_types=1);

namespace Testbench;

use Tempest\Console\ConsoleCommand;

use function Tempest\root_path;
use function Tempest\src_path;
use function Tempest\Support\Filesystem\delete;
use function Tempest\Support\path;

final class TestbenchCommand
{

    #[ConsoleCommand(
        description: 'Remove files that installer created for testing purposes.',
    )]
    public function clean()
    {
        $files_to_delete = [
            root_path('node_modules'),
            (string) path(__DIR__, 'Client'),
            src_path('app.view.php'),
            src_path('Client'),
            root_path('package.json'),
            root_path('package-lock.json'),
            root_path('vite.config.ts'),
        ];

        foreach ($files_to_delete as $file) {
            delete($file, recursive: is_dir($file));
        }
    }
}
