<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Props\LazyProp;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Http\Request;

final class LazyPropTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $lazyProp = new LazyProp(function () {
            return 'A lazy value';
        });

        static::assertSame(
            expected: 'A lazy value',
            actual: $lazyProp(),
        );
    }

    public function test_can_accept_scalar_values(): void
    {
        $lazyProp = new LazyProp(fn() => 'A lazy value');

        static::assertSame(
            expected: 'A lazy value',
            actual: $lazyProp(),
        );
    }

    public function test_can_resolve_bindings_when_invoked(): void
    {
        $lazyProp = new LazyProp(function (Request $request) {
            return $request;
        });

        static::assertInstanceOf(Request::class, $lazyProp());
    }
}
