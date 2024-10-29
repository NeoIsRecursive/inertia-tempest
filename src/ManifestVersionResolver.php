<?php

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\Contracts\InertiaVersionResolver;

use function Tempest\root_path;

final readonly class ManifestVersionResolver implements InertiaVersionResolver
{
    public function __construct(public ?string $manifestPath = null) {}

    public function resolve(): ?string
    {
        $manifestPath = $this->manifestPath ?? root_path('/public/build/manifest.json');

        if (file_exists($manifestPath)) {
            return hash_file('xxh128', $manifestPath);
        }

        return null;
    }
}