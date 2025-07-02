<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Http\Request;

final class MergePropTest extends TestCase
{
    public function test_can_invoke_with_a_callback(): void
    {
        $mergeProp = new AlwaysProp(fn() => 'A merge prop value')->merge();

        static::assertSame(
            expected: 'A merge prop value',
            actual: $mergeProp(),
        );
    }

    public function test_can_invoke_with_a_non_callback(): void
    {
        $mergeProp = new AlwaysProp(['key' => 'value'])->merge();

        static::assertSame(['key' => 'value'], $mergeProp());
    }

    public function test_can_resolve_bindings_when_invoked(): void
    {
        $mergeProp = new AlwaysProp(fn(Request $request) => $request)->merge();

        static::assertInstanceOf(Request::class, $mergeProp());
    }
}
