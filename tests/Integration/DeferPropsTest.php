<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Props\DeferProp;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Http\Request;

final class DeferPropsTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $deferProp = new DeferProp(function (): string {
            return 'A defered value';
        });

        static::assertSame(
            expected: 'A defered value',
            actual: $deferProp(),
        );
    }

    public function test_can_accept_scalar_values(): void
    {
        $deferProp = new DeferProp(fn() => 'A defered value');

        static::assertSame(
            expected: 'A defered value',
            actual: $deferProp(),
        );
    }

    public function test_can_resolve_bindings_when_invoked(): void
    {
        $deferProp = new DeferProp(fn(Request $request) => $request);

        static::assertInstanceOf(Request::class, $deferProp());
    }
}
