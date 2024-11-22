<?php

namespace NeoIsRecursive\Inertia;

use Closure;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\LazyProp;

final class InertiaConfig
{

    public function __construct(
        readonly public string $rootView,
        /** @var class-string<InertiaVersionResolver>  */
        readonly public string $versionResolverClass = ManifestVersionResolver::class,
        /** @var array<AlwaysProp|LazyProp|string|array|Closue> */
        public array $sharedProps = [],
    ) {}

    public function share(string|array $key, LazyProp|AlwaysProp|Closure|string|array $value = null): self
    {
        if (is_array($key)) {
            $this->sharedProps = array_merge($this->sharedProps, $key);
        } else {
            $this->sharedProps[$key] = $value;
        }

        return $this;
    }
}
