<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Http;

use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\PageData;
use NeoIsRecursive\Inertia\Pipeline\PropPipeline;
use NeoIsRecursive\Inertia\Support\Header;
use NeoIsRecursive\Inertia\Views\InertiaBaseView;
use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\View\View;

use function Tempest\Container\get;
use function Tempest\Container\invoke;

final class Component implements Response
{
    use IsResponse;

    private Request $request;

    public function __construct(
        public string $name,
        public array $props = [],
        readonly ?string $rootView = null,
        readonly bool $clearHistory = false,
        readonly bool $encryptHistory = false,
    ) {
        $this->request = get(Request::class);

        if ($this->request->headers->has(Header::INERTIA)) {
            $this->addHeader(Header::INERTIA, value: 'true');
        }

        $this->body = $this->getBody();
    }

    private function getBody(): View|PageData
    {
        $config = get(InertiaConfig::class);
        $component = $this->name;
        $rootView = $this->rootView ?? $config->rootView;
        $version = invoke($config->versionResolver->resolve(...));

        $processedProps = new PropPipeline()->process(
            props: array_merge($config->sharedProps, $this->props),
            request: $this->request,
            component: $component,
        );

        $pageData = new PageData(
            component: $component,
            props: $processedProps->props,
            url: $this->request->uri,
            version: $version,
            clearHistory: $this->clearHistory,
            encryptHistory: $this->encryptHistory,
            propKeysToDefer: $processedProps->deferredProps,
            propsKeysToMerge: $processedProps->mergeProps,
            scrollProps: $processedProps->scrollProps,
        );

        if ($this->request->headers->has(Header::INERTIA)) {
            return $pageData;
        }

        return new InertiaBaseView(path: $rootView, page: $pageData);
    }
}
