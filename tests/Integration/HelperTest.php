<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Integration;

use NeoIsRecursive\Inertia\Inertia;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Http\Response;

use function NeoIsRecursive\Inertia\inertia;

final class HelperTest extends TestCase
{
    public function test_the_helper_function_returns_an_instance_of_the_response_factory(): void
    {
        static::assertInstanceOf(Inertia::class, inertia());
    }

    public function test_the_helper_function_returns_a_response_instance(): void
    {
        static::assertInstanceOf(Response::class, inertia('User/Edit', ['user' => ['name' => 'Jonathan']]));
    }
}
