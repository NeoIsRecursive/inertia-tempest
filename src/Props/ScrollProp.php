<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use Closure;
use NeoIsRecursive\Inertia\Contracts\ProvidesScrollMetadata;
use Override;
use Tempest\Support\Arr\ArrayInterface;
use Tempest\Support\Paginator\PaginatedData;

final class ScrollProp implements Prop
{
    public function __construct(
        public readonly mixed $value,
        public string $pageName,
        public string $wrapper = 'data',
        /** @var ProvidesScrollMetadata|Closure(mixed):ProvidesScrollMetadata|null */
        public ProvidesScrollMetadata|Closure|null $metadata = null,
    ) {}

    #[\Override]
    public function shouldBeEvaluated(PropContext $context): bool
    {
        return !$context->isPartial;
    }

    #[\Override]
    public function resolveValue(PropContext $context): mixed
    {
        return is_callable($this->value) ? $context->container->invoke($this->value) : $this->value;
    }

    #[\Override]
    public function serialize(mixed $resolvedValue): mixed
    {
        if ($resolvedValue instanceof PaginatedData) {
            return [$this->wrapper => $resolvedValue->data];
        }

        if ($resolvedValue instanceof ArrayInterface) {
            return $resolvedValue->toArray();
        }

        return $resolvedValue;
    }

    #[\Override]
    public function getMeta(PropContext $context, mixed $resolvedValue): array
    {
        if ($resolvedValue instanceof NotResolvedValue) {
            return [];
        }

        $metaDataProvider = $this->resolveMetaDataProvider($context, $resolvedValue);

        return [
            'mergeProps' => ["{$context->key}.{$this->wrapper}"],
            'scrollProps' => [
                $context->key => [
                    'pageName' => $metaDataProvider->getPageName(),
                    'previousPage' => $metaDataProvider->getPreviousPage(),
                    'nextPage' => $metaDataProvider->getNextPage(),
                    'currentPage' => $metaDataProvider->getCurrentPage(),
                ],
            ],
        ];
    }

    public function resolveMetaDataProvider(PropContext $context, mixed $resolvedValue): ProvidesScrollMetadata
    {
        $metadata = $this->metadata;

        if ($metadata instanceof ProvidesScrollMetadata) {
            return $metadata;
        }

        if ($metadata instanceof Closure) {
            return $metadata($resolvedValue);
        }

        $pageName = $this->pageName;

        if ($resolvedValue instanceof PaginatedData) {
            return new class($pageName, $resolvedValue) implements ProvidesScrollMetadata {
                public function __construct(
                    private string $pageName,
                    private PaginatedData $value,
                ) {}

                public function getPageName(): string
                {
                    return $this->pageName;
                }

                public function getPreviousPage(): int|string|null
                {
                    return $this->value->previousPage;
                }

                public function getNextPage(): int|string|null
                {
                    return $this->value->nextPage;
                }

                public function getCurrentPage(): int|string|null
                {
                    return $this->value->currentPage;
                }
            };
        }
    }
}
