<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia;

use Closure;
use NeoIsRecursive\Inertia\Contracts\InertiaVersionResolver;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\LazyProp;

final class InertiaConfig
{
    public function __construct(
        public readonly string $rootView,
        /** @var class-string<InertiaVersionResolver>  */
        public readonly InertiaVersionResolver $versionResolver = new ManifestVersionResolver(),
        /** @var array<AlwaysProp|LazyProp|string|array|Closue> */
        public private(set) array $sharedProps = [],
    ) {}

    public function flushSharedProps(): self
    {
        $this->sharedProps = [];

        return $this;
    }

    public function share(string|array $key, LazyProp|AlwaysProp|Closure|string|array|null $value): self
    {
        if (is_array($key)) {
            $this->sharedProps = array_merge($this->sharedProps, $key);
            return $this;
        }

        $this->sharedProps[$key] = $value;

        return $this;
    }
}
