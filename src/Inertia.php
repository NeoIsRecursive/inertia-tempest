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

    public array $sharedProps = [];

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

    public function flushShared(): void
    {
        $this->sharedProps = [];
    }

    public function getVersion(): string
    {
        return $this->config->resolveVersion();
    }

    public function render(string $component, array $props = []): InertiaResponse
    {
        return new InertiaResponse(
            request: $this->container->get(Request::class),
            page: $component,
            component: array_merge(
                $this->config->resolveDefaultSharedProps(),
                $this->sharedProps,
                $props
            ),
            rootView: $this->config->rootView,
            version: $this->getVersion()
        );
    }

    public function location(string|Redirect $url): Response
    {
        $isInertiaRequest = isset($this->container->get(Request::class)->getHeaders()[Header::INERTIA]);


        if ($isInertiaRequest) {
            if ($url instanceof Redirect) {
                $url = $url->getHeader('Location')->values[0];
            }

            return new GenericResponse(
                status: Status::CONFLICT,
                body: '',
                headers: [
                    Header::LOCATION => $url,
                ]
            );
        }

        return $url instanceof Redirect ? $url : new Redirect($url);
    }
}
