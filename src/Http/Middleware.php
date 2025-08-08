<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Http;

use NeoIsRecursive\Inertia\Inertia;
use NeoIsRecursive\Inertia\Support\Header;
use Override;
use Tempest\Core\Priority;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

#[Priority(Priority::HIGH)]
final class Middleware implements HttpMiddleware
{
    public function __construct(
        private Inertia $inertia,
        private Session $session,
    ) {}

    #[Override]
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->addHeader(
            key: 'Vary',
            value: Header::INERTIA,
        );

        if (!$request->headers->has(Header::INERTIA)) {
            return $response;
        }

        $clientVersion = $request->headers->get(Header::VERSION) ?? '';

        if ($request->method === Method::GET && $clientVersion !== $this->inertia->version) {
            $this->session->reflash();

            return $this->inertia->location($request->uri);
        }

        if (
            $response->status === Status::FOUND &&
                in_array($request->method, [Method::POST, Method::PUT, Method::PATCH], strict: true)
        ) {
            $response->setStatus(Status::SEE_OTHER);
        }

        return $response;
    }
}
