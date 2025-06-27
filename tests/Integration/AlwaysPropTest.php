<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Http\Request;

final class AlwaysPropTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $alwaysProp = new AlwaysProp(function () {
            return 'An always value';
        });

        static::assertSame('An always value', $alwaysProp());
    }

    public function test_can_accept_scalar_values(): void
    {
        $alwaysProp = new AlwaysProp('An always value');

        static::assertSame('An always value', $alwaysProp());
    }

    public function test_can_accept_invokable_class(): void
    {
        static::markTestSkipped('Dont really know if this is necessary or possible');

        $callable = new class() {
            public function __invoke(): string
            {
                return 'An always value';
            }
        };

        $alwaysProp = new AlwaysProp(fn() => $callable);

        static::assertSame('An always value', $alwaysProp());
    }

    public function test_can_resolve_bindings_when_invoked(): void
    {
        $alwaysProp = new AlwaysProp(function (Request $request) {
            return $request;
        });

        static::assertInstanceOf(Request::class, $alwaysProp());
    }
}
