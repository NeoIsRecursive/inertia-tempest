<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\Contracts\InertiaVersionResolver;
use Override;
use Tempest\Container\Container;

use function Tempest\root_path;

final readonly class ManifestVersionResolver implements InertiaVersionResolver
{
    public function __construct(
        public ?string $manifestPath = null,
    ) {}

    #[Override]
    public function resolve(Container $container): string
    {
        $manifestPath = $this->manifestPath ?? root_path('/public/build/manifest.json');

        if (file_exists($manifestPath)) {
            $hash = hash_file(
                algo: 'xxh128',
                filename: $manifestPath,
            );

            if (!$hash) {
                return '';
            }

            return $hash;
        }

        return '';
    }
}
