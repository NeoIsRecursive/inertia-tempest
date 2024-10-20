<?php

namespace NeoIsRecursive\Inertia;

use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Http\InertiaResponse;
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

    private array $sharedProps = [];

    public function __construct(
        private Container $container,
        private InertiaConfig $config
    ) {}

    public function share(string|array $key, ?string $value = null): void
    {
        if (is_array($key)) {
            $this->sharedProps = array_merge($this->sharedProps, $key);
        } else {
            $this->sharedProps[$key] = $value;
        }
    }

    // public function flushShared(): void
    // {
    //     $this->sharedProps = [];
    // }

    public function getVersion(): string
    {
        $version = is_callable($this->config->version)
            ? $this->container->invoke($this->config->version)
            : $this->config->version;

        return (string) $version;
    }

    public function render(string $component, array $props = []): InertiaResponse
    {
        return new InertiaResponse(
            request: $this->container->get(Request::class),
            page: $component,
            props: array_merge(
                $this->container->invoke($this->config->getSharedProps),
                $this->sharedProps,
                $props
            ),
            rootView: $this->config->rootView,
            version: $this->getVersion()
        );
    }

    public function location(string $url): Response
    {
        if (isset($this->container->get(Request::class)->getHeaders()[Header::INERTIA])) {
            return new GenericResponse(
                status: Status::CONFLICT,
                body: '',
                headers: [
                    Header::LOCATION => $url,
                ]
            );
        }

        return new Redirect($url);
    }
}
