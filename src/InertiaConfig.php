<?php

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\Contracts\InertiaVersionResolver;
use NeoIsRecursive\Inertia\Contracts\SharedPropsResolver;

use function Tempest\get;

final readonly class InertiaConfig
{

    public function __construct(
        public string $rootView,
        /**
         * @property class-string<InertiaVersionResolver>  $versionResolver
         */
        public string $versionResolver,
        /** @property class-string<SharedPropsResolver> $sharedPropsResolver */
        public string $sharedPropsResolver,
    ) {}

    public function resolveVersion(): string
    {
        $string = $this->versionResolver;

        /** @var InertiaVersionResolver */
        $resolver = get($this->versionResolver);
        return $resolver->resolve();
    }

    public function resolveDefaultSharedProps(): array
    {
        /** @var SharedPropsResolver */
        $resolver = get($this->sharedPropsResolver);

        return $resolver->resolve();
    }
}
