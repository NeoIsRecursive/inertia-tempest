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
        public null|array $deferredProps = null,
        public null|array $mergeProps = null,
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

        if ($this->deferredProps !== null) {
            $data['deferredProps'] = $this->deferredProps;
        }

        if ($this->mergeProps !== null) {
            $data['mergeProps'] = $this->mergeProps;
        }

        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function render(string $id): HtmlString
    {
        return HtmlString::createTag(
            tag: 'div',
            attributes: [
                'id' => $id,
                'data-page' => htmlentities(json_encode($this, JSON_THROW_ON_ERROR)),
            ],
        );
    }
}
