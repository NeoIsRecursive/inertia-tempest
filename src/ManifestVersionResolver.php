<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\Contracts\InertiaVersionResolver;
use Tempest\Container\Container;

use function Tempest\root_path;

final readonly class ManifestVersionResolver implements InertiaVersionResolver
{
    public function __construct(
        public null|string $manifestPath = null,
    ) {}

    public function resolve(Container $container): string
    {
        $manifestPath = $this->manifestPath ?? root_path('/public/build/manifest.json'); // @mago-expect best-practices/literal-named-argument

        if (file_exists($manifestPath)) {
            return hash_file(
                algo: 'xxh128',
                filename: $manifestPath,
            );
        }

        return '';
    }
}
