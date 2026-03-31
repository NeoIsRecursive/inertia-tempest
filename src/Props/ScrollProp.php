<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use Closure;
use NeoIsRecursive\Inertia\Concerns\IsCallableProp;
use NeoIsRecursive\Inertia\Concerns\IsMergeableProp;
use NeoIsRecursive\Inertia\Contracts\CallableProp;
use NeoIsRecursive\Inertia\Contracts\MergeableProp;
use NeoIsRecursive\Inertia\Contracts\ProvidesScrollMetadata;
use Override;
use Tempest\Support\Paginator\PaginatedData;

final class ScrollProp implements CallableProp, MergeableProp
{
    use IsMergeableProp;
    use IsCallableProp;

    public mixed $resolved = null;
    public bool $shouldMerge = true;

    public function __construct(
        public readonly mixed $value,
        public string $pageName,
        public string $wrapper = 'data',
        /** @var null|ProvidesScrollMetadata|Closure<mixed,ProvidesScrollMetadata> */
        public null|ProvidesScrollMetadata|Closure $metadata = null,
    ) {}

    public function mergeKey(string|int $key): string|int
    {
        if (!is_string($key) || $this->wrapper === '') {
            return $key;
        }

        return sprintf('%s.%s', $key, $this->wrapper);
    }

    protected function resolveValue(): mixed
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $this->resolved = $this->resolveCallablePropValue($this->value);

        return $this->resolved;
    }

    /**
     * Resolve the scroll metadata provider.
     */
    protected function resolveMetadataProvider(): ProvidesScrollMetadata
    {
        if ($this->metadata instanceof ProvidesScrollMetadata) {
            return $this->metadata;
        }

        $value = $this->resolveValue();

        if ($this->metadata === null) {
            $pageName = $this->pageName;

            if ($value instanceof PaginatedData) {
                return new class($pageName, $value) implements ProvidesScrollMetadata {
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

        return call_user_func($this->metadata, $value);
    }

    /**
     * Get the pagination meta information.
     *
     * @return array{pageName: string, previousPage: int|string|null, nextPage: int|string|null, currentPage: int|string|null}
     */
    public function metadata(): array
    {
        $metadataProvider = $this->resolveMetadataProvider();

        return [
            'pageName' => $metadataProvider->getPageName(),
            'previousPage' => $metadataProvider->getPreviousPage(),
            'nextPage' => $metadataProvider->getNextPage(),
            'currentPage' => $metadataProvider->getCurrentPage(),
        ];
    }

    #[Override]
    public function __invoke(): mixed
    {
        $value = $this->resolveValue();

        if ($value instanceof PaginatedData) {
            return [
                $this->wrapper => $value->data,
            ];
        }

        return $value;
    }
}
