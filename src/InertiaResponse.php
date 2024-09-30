<?php

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\Support\Header;
use NeoIsRecursive\Inertia\Views\InertiaBaseView;
use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class InertiaResponse implements Response
{
    use IsResponse;

    public function __construct(Request $request, string $page, array $props, string $rootView, string $version)
    {
        // TODO: Evaluate props

        $page = [
            'component' => $page,
            'props' => $props,
            'url' => $request->getPath(),
            'version' => $version,
        ];

        if (array_key_exists(Header::INERTIA, $request->getHeaders()) && $request->getHeaders()[Header::INERTIA] === 'true') {
            $this->status = Status::OK;

            $this->body = $page;

            $this->addHeader(Header::INERTIA, 'true');
            return;
        }

        $this->body = new InertiaBaseView(
            view: $rootView,
            pageData: $page,
        );
    }
}
