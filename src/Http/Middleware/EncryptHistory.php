<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Http\Middleware;

use NeoIsRecursive\Inertia\Inertia;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

#[SkipDiscovery]
final readonly class EncryptHistory implements HttpMiddleware
{
    public function __construct(
        private Inertia $inertia,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $this->inertia->encryptHistory();

        return $next($request);
    }
}
