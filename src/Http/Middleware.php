<?php

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
    public function __construct(private Inertia $inertia, private Container $container) {}

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

        if (!array_key_exists(Header::INERTIA, $request->getHeaders())) {
            return $response;
        }

        $versionHeaderValue = $request->getHeaders()[Header::VERSION] ?? '';

        if ($request->getMethod() === Method::GET && $versionHeaderValue !== $this->inertia->version) {
            // TODO: reflash session data

            return $this->inertia->location($request->getUri());
        }

        if ($response->getStatus() === Status::FOUND && in_array($request->getMethod(), [Method::POST, Method::PUT, Method::PATCH])) {
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
