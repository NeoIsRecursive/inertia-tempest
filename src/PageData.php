<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia;

use JsonSerializable;
use Override;

final readonly class PageData implements JsonSerializable
{
    // @mago-expect lint:excessive-parameter-list
    public function __construct(
        public string $component,
        public array $props,
        public string $url,
        public string $version,
        public bool $clearHistory,
        public bool $encryptHistory,
        public array $meta,
    ) {}

    public function toArray(): array
    {
        $data = [
            'component' => $this->component,
            'props' => $this->props,
            'url' => $this->url,
            'version' => $this->version,
        ];

        if ($this->clearHistory) {
            $data['clearHistory'] = $this->clearHistory;
        }
        if ($this->encryptHistory) {
            $data['encryptHistory'] = $this->encryptHistory;
        }

        return array_merge($data, $this->meta);
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
