<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Http;

use NeoIsRecursive\Inertia\Inertia;
use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Support\Header;
use Override;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Support\Priority;

#[Priority(Priority::HIGH)]
final class Middleware implements HttpMiddleware
{
    public function __construct(
        private InertiaConfig $config,
        private Inertia $inertia,
        private Session $session,
    ) {}

    #[Override]
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $response = $next($request);

        $response = $response->addHeader(key: 'Vary', value: Header::INERTIA);

        $isRedirect = $response->status->isRedirect();

        if ($isRedirect) {
            $this->session->reflash();
        }

        if (!$request->headers->has(Header::INERTIA)) {
            return $response;
        }

        $clientVersion = $request->headers->get(Header::VERSION) ?? '';

        if ($request->method === Method::GET && $clientVersion !== $this->config->resolveVersion()) {
            $this->session->reflash();

            return $this->inertia->location($request->uri);
        }

        if (
            $response->status === Status::FOUND
            && in_array($request->method, [Method::DELETE, Method::PUT, Method::PATCH], strict: true)
        ) {
            return $response->setStatus(Status::SEE_OTHER);
        }

        return $response;
    }
}
