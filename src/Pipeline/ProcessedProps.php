<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline;

final readonly class ProcessedProps
{
    /**
     * @param array<string, mixed> $props
     */
    public function __construct(
        public array $props,
        public ?array $deferredProps = null,
        public ?array $mergeProps = null,
        public ?array $scrollProps = null,
        public ?array $onceProps = null,
    ) {}
}
