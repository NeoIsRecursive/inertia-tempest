<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Pipeline;

use NeoIsRecursive\Inertia\Support\Header;
use Tempest\Http\Request;

final readonly class PropPipelineContext
{
    /**
     * @mago-expect lint:excessive-parameter-list
     * 
     * @param array<string,mixed> $originalProps
     * @param array<string,mixed> $renderableProps
     * @param array<string,mixed> $evaluatedProps
     */
    public function __construct(
        public Request $request,
        public string $component,
        public array $originalProps,
        public array $renderableProps = [],
        public array $evaluatedProps = [],
        public ?array $deferredProps = null,
        public ?array $mergeProps = null,
        public ?array $scrollProps = null,
        public ?array $onceProps = null,
    ) {}

    public function isPartial(): bool
    {
        return $this->request->headers->get(Header::PARTIAL_COMPONENT) === $this->component;
    }

    /**
     * @param array{
     *     request?: Request,
     *     component?: string,
     *     originalProps?: array<string, mixed>,
     *     renderableProps?: array<string, mixed>,
     *     evaluatedProps?: array<string, mixed>,
     *     deferredProps?: ?array,
     *     mergeProps?: ?array,
     *     scrollProps?: ?array,
     *     onceProps?: ?array
     * } $properties
     */
    public function with(array $properties): self
    {
        return clone($this, $properties);
    }
}
