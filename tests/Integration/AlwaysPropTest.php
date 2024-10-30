<?php

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Http\Request;

class AlwaysPropTest extends TestCase
{
    public function test_can_invoke(): void
    {
        $alwaysProp = new AlwaysProp(function () {
            return 'An always value';
        });

        $this->assertSame('An always value', $alwaysProp());
    }

    public function test_can_accept_scalar_values(): void
    {
        $alwaysProp = new AlwaysProp('An always value');

        $this->assertSame('An always value', $alwaysProp());
    }

    public function test_can_accept_invokable_class(): void
    {
        $this->markTestSkipped('Dont really know if this is necessary or possible');

        $callable = new class() {
            public function __invoke()
            {
                return 'An always value';
            }
        };

        $alwaysProp = new AlwaysProp(fn() => $callable);

        $this->assertSame('An always value', $alwaysProp());
    }

    public function test_can_resolve_bindings_when_invoked(): void
    {
        $alwaysProp = new AlwaysProp(function (Request $request) {
            return $request;
        });

        $this->assertInstanceOf(Request::class, $alwaysProp());
    }
}
