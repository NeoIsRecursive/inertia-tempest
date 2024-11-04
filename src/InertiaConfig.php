<?php

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\Contracts\InertiaVersionResolver;
use NeoIsRecursive\Inertia\Contracts\SharedPropsResolver;

use function Tempest\get;

final readonly class InertiaConfig
{

    public function __construct(
        public string $rootView,
        /** @property class-string<InertiaVersionResolver>  */
        public string $versionResolverClass = ManifestVersionResolver::class,
        /** @property class-string<SharedPropsResolver> */
        public string $defaultPropsResolverClass = DefaultSharedPropResolver::class,
    ) {}

    public function resolveVersion(): string
    {
        return get($this->versionResolverClass)->resolve();
    }

    public function resolveDefaultSharedProps(): array
    {
        return get($this->defaultPropsResolverClass)->resolve();
    }
}
