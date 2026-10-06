<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Props;

use NeoIsRecursive\Inertia\Support\Header;
use Tempest\Container\Container;
use Tempest\Http\Request;

use function Tempest\Support\arr;

final class PropContext
{
    public function __construct(
        public readonly Container $container,
        public readonly string $component,
        public readonly string $key,
        public readonly Request $request,
    ) {}

    public bool $isPartial {
        get => $this->request->headers->get(Header::PARTIAL_COMPONENT) === $this->component;
    }

    private static function splitHeaders(array $array): array
    {
        return arr($array)
            ->flatMap(static fn($header) => explode(',', $header))
            ->values()
            ->toArray();
    }

    /** @var list<string> */
    public array $except {
        get => static::splitHeaders($this->request->headers->getHeader(Header::PARTIAL_EXCEPT)->values);
    }

    /** @var list<string> */
    public array $only {
        get => static::splitHeaders($this->request->headers->getHeader(Header::PARTIAL_ONLY)->values);
    }
}
