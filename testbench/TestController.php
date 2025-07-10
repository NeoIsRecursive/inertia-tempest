<?php

declare(strict_types=1);

namespace Testbench;

use NeoIsRecursive\Inertia\Http\InertiaResponse;
use Tempest\Router\Get;

use function NeoIsRecursive\Inertia\inertia;

final readonly class TestController
{
    #[Get('/')]
    public function examplePage(): InertiaResponse
    {
        return inertia(
            component: 'example-page',
        );
    }
}
