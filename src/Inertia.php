<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia;

use Closure;
use NeoIsRecursive\Inertia\Http\InertiaResponse;
use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\DeferProp;
use NeoIsRecursive\Inertia\Props\LazyProp;
use NeoIsRecursive\Inertia\Support\Header;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Http\GenericResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;

#[Singleton]
final class Inertia
{
    public function __construct(
        private Session $session,
        private Container $container,
        private InertiaConfig $config,
    ) {}

    public function share(
        string|array $key,
        LazyProp|AlwaysProp|DeferProp|Closure|string|array|null $value = null,
    ): self {
        $this->config->share($key, $value);

        return $this;
    }

    public function flushShared(): self
    {
        $this->config->flushSharedProps();

        return $this;
    }

    public string $version {
        get => $this->container->invoke($this->config->versionResolver->resolve(...));
    }

    public function render(string $component, array $props = []): InertiaResponse
    {
        return new InertiaResponse(
            request: $this->container->get(Request::class),
            component: $component,
            props: array_merge($this->config->sharedProps, $props),
            rootView: $this->config->rootView,
            version: $this->version,
            clearHistory: $this->session->get(
                key: 'inertia.clear_history',
                default: false,
            ),
            encryptHistory: $this->session->get(
                key: 'inertia.encrypt_history',
                default: false,
            ),
        );
    }

    public function encryptHistory(): self
    {
        $this->session->flash(
            key: 'inertia.encrypt_history',
            value: true,
        );

        return $this;
    }

    public function clearHistory(): self
    {
        $this->session->flash(
            key: 'inertia.clear_history',
            value: true,
        );

        return $this;
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
