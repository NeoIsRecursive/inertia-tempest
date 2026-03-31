<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Http;

use NeoIsRecursive\Inertia\PageData;
use NeoIsRecursive\Inertia\Pipeline\PropPipeline;
use NeoIsRecursive\Inertia\Support\Header;
use NeoIsRecursive\Inertia\Views\InertiaBaseView;
use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;

final class InertiaResponse implements Response
{
    use IsResponse;

    /**
     * @mago-expect lint:excessive-parameter-list
     *
     * @param array<string,mixed> $props
     */
    public function __construct(
        readonly Request $request,
        readonly string $component,
        readonly array $props,
        readonly string $rootView,
        readonly string $version,
        readonly bool $clearHistory = false,
        readonly bool $encryptHistory = false,
    ) {
        $processedProps = new PropPipeline()->process(props: $props, request: $request, component: $component);

        $pageData = new PageData(
            component: $component,
            props: $processedProps->props,
            url: $request->uri,
            version: $version,
            clearHistory: $clearHistory,
            encryptHistory: $encryptHistory,
            propKeysToDefer: $processedProps->deferredProps,
            propsKeysToMerge: $processedProps->mergeProps,
            scrollProps: $processedProps->scrollProps,
            onceProps: $processedProps->onceProps,
        );

        if ($request->headers->has(Header::INERTIA)) {
            $this->addHeader(Header::INERTIA, value: 'true');
            $this->body = $pageData;
            return;
        }

        $this->body = new InertiaBaseView(path: $rootView, page: $pageData);
    }
}
