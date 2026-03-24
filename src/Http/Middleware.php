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

        if ($request->method === Method::GET && $clientVersion !== $this->inertia->version) {
            $this->session->reflash();

            return $this->inertia->location($request->uri);
        }

        if (
            $response->status === Status::FOUND
            && in_array($request->method, [Method::DELETE, Method::PUT, Method::PATCH], strict: true)
        ) {
            return $response->setStatus(Status::SEE_OTHER);
        }

        if ($isRedirect && $this->redirectHasFragment($response) && !$this->prefetch($request)) {
            // TODO(neo): ensure this works the same as the laravel adapter
            return $this->inertia->location(response);
        }

        return $response;
    }

    /**
     * Determine if the redirect response contains a URL fragment.
     */
    private function redirectHasFragment(Response $response): bool
    {
        return str_contains($response->getHeader('location')->first() ?? '', '#');
    }

    private function prefetch(Request $request)
    {
        return (
            strcasecmp($request->headers->get('HTTP_X_MOZ', ''), 'prefetch') === 0
            || strcasecmp($request->headers->get('Purpose', ''), 'prefetch') === 0
            || strcasecmp($request->headers->get('Sec-Purpose', ''), 'prefetch') === 0
        );
    }
}
