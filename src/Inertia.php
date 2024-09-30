<?php

namespace NeoIsRecursive\Inertia;

use Closure;
use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\LazyProp;
use NeoIsRecursive\Inertia\Support\Header;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Http\GenericResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Status;

use function Tempest\get;

#[Singleton]
final class Inertia
{

    private array $sharedProps = [];

    public function __construct(
        private Container $container,
        private InertiaConfig $config
    ) {}

    // /**
    //  * @param string|array $key
    //  */
    public function share($key, $value = null): void
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
        $version = $this->config->version instanceof Closure
            ? $this->container->invoke($this->config->version)
            : $this->config->version;

        return (string) $version;
    }

    public function lazy(callable $callback): LazyProp
    {
        return new LazyProp($callback);
    }

    public function always($value): AlwaysProp
    {
        return new AlwaysProp($value);
    }

    /**
     * @param array|Arrayable $props
     */
    public function render(string $component, array $props = []): InertiaResponse
    {
        $props = array_merge(call_user_func($this->config->getSharedProps), $this->sharedProps, $props);

        return new InertiaResponse(
            request: $this->container->get(Request::class),
            page: $component,
            props: $props,
            rootView: $this->config->rootView,
            version: $this->getVersion()
        );
    }

    /**
     * @param string|SymfonyRedirect $url
     */
    public function location($url): Response
    {
        if (isset(get(Request::class)->getHeaders()[Header::INERTIA])) {
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
