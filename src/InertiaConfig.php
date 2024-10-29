<?php

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\Conracts\InertiaVersionResolver;
use NeoIsRecursive\Inertia\Conracts\SharedPropsResolver;

use function Tempest\get;

final readonly class InertiaConfig
{

    public function __construct(
        public string $rootView,
        /** @var class-string<InertiaVersionResolver> $version */
        public string $versionResolver,
        /** @var class-string<SharedPropsResolver> $sharedProps */
        public string $sharedPropsResolver,
    ) {}

    public function resolveVersion(): ?string
    {
        return get($this->versionResolver)->resolve();
    }

    public function resolveSharedProps(): array
    {
        return get($this->sharedPropsResolver)->resolve();
    }
}
