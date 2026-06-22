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
        public ?array $propKeysToDefer = null,
        public ?array $propsKeysToMerge = null,
        public ?array $scrollProps = null,
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

        if ($this->propKeysToDefer !== null) {
            $data['deferredProps'] = $this->propKeysToDefer;
        }

        if ($this->propsKeysToMerge !== null) {
            $data['mergeProps'] = $this->propsKeysToMerge;
        }

        if ($this->scrollProps !== null) {
            $data['scrollProps'] = $this->scrollProps;
        }

        // if ($this->matchPropsOn !== null) {
        //     $data['matchPropsOn'] = $this->matchPropsOn;
        // }

        return $data;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
