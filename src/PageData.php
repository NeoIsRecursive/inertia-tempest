<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia;

use JsonSerializable;
use Tempest\Support\Html\HtmlString;

final readonly class PageData implements JsonSerializable
{
    // @mago-expect maintainability/excessive-parameter-list
    public function __construct(
        public string $component,
        public array $props,
        public string $url,
        public string $version,
        public bool $clearHistory,
        public bool $encryptHistory,
        public ?array $propKeysToDefer = null,
        public ?array $propsKeysToMerge = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'component' => $this->component,
            'props' => $this->props,
            'url' => $this->url,
            'version' => $this->version,
            'clearHistory' => $this->clearHistory,
            'encryptHistory' => $this->encryptHistory,
        ];

        if ($this->propKeysToDefer !== null) {
            $data['deferredProps'] = $this->propKeysToDefer;
        }

        if ($this->propsKeysToMerge !== null) {
            $data['mergeProps'] = $this->propsKeysToMerge;
        }

        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
