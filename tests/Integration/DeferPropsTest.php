<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Props\DeferProp;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Router\Request;

final class DeferPropsTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $deferProp = new DeferProp(function () {
            return 'A lazy value';
        });

        static::assertSame('A lazy value', $deferProp());
    }

    public function test_can_accept_scalar_values(): void
    {
        $deferProp = new DeferProp(fn() => 'A lazy value');

        static::assertSame('A lazy value', $deferProp());
    }

    public function test_can_resolve_bindings_when_invoked(): void
    {
        $deferProp = new DeferProp(function (Request $request) {
            return $request;
        });

        static::assertInstanceOf(Request::class, $deferProp());
    }
}
