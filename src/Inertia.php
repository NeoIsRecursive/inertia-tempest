<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia;

use Closure;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\DeferProp;
use NeoIsRecursive\Inertia\Props\OptionalProp;
use NeoIsRecursive\Inertia\Support\Header;
use Tempest\Http\GenericResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;

final class Inertia
{
    public const string CLEAR_HISTORY_KEY = '#inertia.clear_history';
    public const string ENCRYPT_HISTORY_KEY = '#inertia.encrypt_history';

    public function __construct(
        private Session $session,
        private Request $request,
        private InertiaConfig $config,
    ) {}

    /**
     * @param string|array<string, mixed> $key
     */
    public function share(string|array $key, mixed $value = null): self
    {
        $this->config->share($key, $value);

        return $this;
    }

    public static function optional(MethodReflector|FunctionReflector|Closure $callback): OptionalProp
    {
        return new OptionalProp($callback);
    }

    public static function defer(MethodReflector|FunctionReflector|Closure $callback): DeferProp
    {
        return new DeferProp($callback);
    }

    public static function always(mixed $value): AlwaysProp
    {
        return new AlwaysProp($value);
    }

    public function flushShared(): self
    {
        $this->config->flushSharedProps();

        return $this;
    }

    public function encryptHistory(): self
    {
        $this->session->flash(key: self::ENCRYPT_HISTORY_KEY, value: true);

        return $this;
    }

    public function clearHistory(): self
    {
        $this->session->flash(key: self::CLEAR_HISTORY_KEY, value: true);

        return $this;
    }

    public function location(string|Redirect $url): Response
    {
        $isInertiaRequest = $this->request->headers->has(Header::INERTIA);

        if ($isInertiaRequest) {
            if ($url instanceof Redirect) {
                /** @var string */
                $url = $url->getHeader(name: 'Location')?->first() ?? '/';
            }

            return new GenericResponse(status: Status::CONFLICT, body: '', headers: [
                Header::LOCATION => $url,
            ]);
        }

        return $url instanceof Redirect ? $url : new Redirect($url);
    }
}
