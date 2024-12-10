<?php

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Props\LazyProp;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Router\Request;

class LazyPropTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $lazyProp = new LazyProp(function () {
            return 'A lazy value';
        });

        $this->assertSame('A lazy value', $lazyProp());
    }

    public function test_can_accept_scalar_values(): void
    {
        $lazyProp = new LazyProp(fn() => 'A lazy value');

        $this->assertSame('A lazy value', $lazyProp());
    }

    public function test_can_resolve_bindings_when_invoked(): void
    {
        $lazyProp = new LazyProp(function (Request $request) {
            return $request;
        });

        $this->assertInstanceOf(Request::class, $lazyProp());
    }
}
