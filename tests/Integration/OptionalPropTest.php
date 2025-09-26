<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Props\OptionalProp;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Http\Request;

final class OptionalPropTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $optionalProp = new OptionalProp(function (): string {
            return 'A optional value';
        });

        static::assertSame(
            expected: 'A optional value',
            actual: $optionalProp(),
        );
    }

    public function test_can_accept_scalar_values(): void
    {
        $optionalProp = new OptionalProp(fn() => 'A optional value');

        static::assertSame(
            expected: 'A optional value',
            actual: $optionalProp(),
        );
    }

    public function test_can_resolve_bindings_when_invoked(): void
    {
        $optionalProp = new OptionalProp(fn(Request $request) => $request);

        static::assertInstanceOf(Request::class, $optionalProp());
    }
}
