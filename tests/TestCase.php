<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests;

use Tempest\Framework\Testing\IntegrationTest;

abstract class TestCase extends IntegrationTest
{
    protected string $root = __DIR__ . '/../../';
}
