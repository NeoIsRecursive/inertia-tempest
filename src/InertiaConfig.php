<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\Contracts\InertiaVersionResolver;

final class InertiaConfig
{
    /**
     * @param array<string, mixed> $sharedProps
     */
    public function __construct(
        public readonly string $rootView,
        public readonly InertiaVersionResolver $versionResolver = new ManifestVersionResolver(),
        public private(set) array $sharedProps = [],
    ) {}

    public function flushSharedProps(): self
    {
        $this->sharedProps = [];

        return $this;
    }

    /**
     * @param (string|array<string, mixed>) $key
     * @param ($key is string ? mixed : null) $value
     */
    public function share(string|array $key, mixed $value = null): self
    {
        if (is_array($key)) {
            $this->sharedProps = array_merge($this->sharedProps, $key);
            return $this;
        }

        $this->sharedProps[$key] = $value;

        return $this;
    }
}
