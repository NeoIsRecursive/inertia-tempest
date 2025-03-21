<?php

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Router\Request;

final class MergePropTest extends TestCase
{
    public function test_can_invoke_with_a_callback(): void
    {
        $mergeProp = new AlwaysProp(fn() =>  'A merge prop value')->merge();

        $this->assertSame('A merge prop value', $mergeProp());
    }

    public function test_can_invoke_with_a_non_callback(): void
    {
        $mergeProp = new AlwaysProp(['key' => 'value'])->merge();

        $this->assertSame(['key' => 'value'], $mergeProp());
    }

    public function test_can_resolve_bindings_when_invoked(): void
    {
        $mergeProp = new AlwaysProp(
            fn(Request $request) => $request
        )->merge();

        $this->assertInstanceOf(Request::class, $mergeProp());
    }
}
