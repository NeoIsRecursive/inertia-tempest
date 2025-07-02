<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\Http\InertiaResponse;
use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Support\Header;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Http\GenericResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Status;

#[Singleton]
final class Inertia
{
    public function __construct(
        private Container $container,
        private InertiaConfig $config,
    ) {}

    public function share(string|array $key, null|string $value = null): void
    {
        $this->config->share($key, $value);
    }

    public function flushShared(): void
    {
        $this->config->flushSharedProps();
    }

    public string $version {
        get => $this->container->invoke($this->config->versionResolver->resolve(...));
    }

    public function render(string $page, array $props = []): InertiaResponse
    {
        return new InertiaResponse(
            request: $this->container->get(Request::class),
            page: $page,
            props: array_merge($this->config->sharedProps, $props),
            rootView: $this->config->rootView,
            version: $this->version,
        );
    }

    public function location(string|Redirect $url): Response
    {
        $isInertiaRequest = $this
            ->container->get(Request::class)
            ->headers->has(Header::INERTIA);

        if ($isInertiaRequest) {
            if ($url instanceof Redirect) {
                $url = $url->getHeader(name: 'Location')->values[0];
            }

            return new GenericResponse(
                status: Status::CONFLICT,
                body: '',
                headers: [
                    Header::LOCATION => $url,
                ],
            );
        }

        return ($url instanceof Redirect) ? $url : new Redirect($url);
    }
}
