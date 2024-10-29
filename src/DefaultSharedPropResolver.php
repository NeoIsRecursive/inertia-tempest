<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\Contracts\SharedPropsResolver;
use NeoIsRecursive\Inertia\Support\ResolveErrorProps;
use Tempest\Auth\Authenticator;
use Tempest\Container\Container;

final readonly class DefaultSharedPropResolver implements SharedPropsResolver
{
    public function __construct(
        private Container $container,
        private Authenticator $auth,
        private ResolveErrorProps $resolveErrorProps,
    ) {}

    public function resolve(): array
    {
        return [
            'user' => $this->auth->currentUser(),
            ...$this->resolveErrorProps->resolve(),
        ];
    }
}
