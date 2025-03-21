<?php

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Props\DeferredProp;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Router\Request;

final class DeferPropsTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $deferProp = new DeferredProp(function () {
            return 'A lazy value';
        });

        $this->assertSame('A lazy value', $deferProp());
    }

    public function test_can_accept_scalar_values(): void
    {
        $deferProp = new DeferredProp(fn() => 'A lazy value');

        $this->assertSame('A lazy value', $deferProp());
    }

    public function test_can_resolve_bindings_when_invoked(): void
    {
        $deferProp = new DeferredProp(function (Request $request) {
            return $request;
        });

        $this->assertInstanceOf(Request::class, $deferProp());
    }
}
