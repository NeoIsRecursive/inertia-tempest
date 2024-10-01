<?php

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\Support\Header;
use Tempest\Http\HttpMiddleware;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Status;

final class Middleware implements HttpMiddleware
{
    public function __construct(private Inertia $inertia) {}

    public function __invoke(Request $request, callable $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->addHeader('Vary', Header::INERTIA);

        if (!array_key_exists(Header::INERTIA, $request->getHeaders())) {
            return $response;
        }

        $versionHeaderValue = $request->getHeaders()[Header::VERSION] ?? '';

        if ($request->getMethod() === Method::GET && $versionHeaderValue !== $this->inertia->getVersion()) {
            // TODO: reflash session data
            // if ($request->session()) {
            //     $request->session()->reflash();
            // }

            return $this->inertia->location($request->getUri());
        }

        if ($response->getStatus() === Status::FOUND && in_array($request->getMethod(), [Method::POST, Method::PUT, Method::PATCH])) {
            // TODO: set status to 303
            // return new Redirect($response->getHeader('Location'));
        }

        return $response;
    }
}
