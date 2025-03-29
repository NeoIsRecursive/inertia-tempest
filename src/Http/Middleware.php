<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Http;

use NeoIsRecursive\Inertia\Inertia;
use NeoIsRecursive\Inertia\Support\Header;
use Tempest\Container\Container;
use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;
use Tempest\Http\Method;
use Tempest\Http\Status;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\Request;
use Tempest\Router\Response;
use Tempest\Router\Router;

final class Middleware implements HttpMiddleware
{
    public function __construct(
        private Inertia $inertia,
        private Container $container,
    ) {}

    #[EventHandler(event: KernelEvent::BOOTED)]
    public function register(): void
    {
        $router = $this->container->get(Router::class);
        $router->addMiddleware(self::class);
    }

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->addHeader('Vary', Header::INERTIA);

        if (!$request->headers->has(Header::INERTIA)) {
            return $response;
        }

        $versionHeaderValue = $request->headers->get(Header::VERSION) ?? '';

        if ($request->method === Method::GET && $versionHeaderValue !== $this->inertia->version) {
            // TODO: reflash session data

            return $this->inertia->location($request->uri);
        }

        if (
            $response->status === Status::FOUND &&
            in_array($request->method, [Method::POST, Method::PUT, Method::PATCH], strict: true)
        ) {
            // TODO: set status to 303
            // return new GenericResponse(
            //     status: Status::SEE_OTHER,
            //     headers: [
            //         'Location' => $response->getHeader('Location'),
            //     ]
            // );
        }

        return $response;
    }
}
