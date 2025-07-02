<?php

declare(strict_types=1);

use NeoIsRecursive\Inertia\ManifestVersionResolver;
use NeoIsRecursive\Inertia\Tests\TestCase;
use Tempest\Container\GenericContainer;

final class VersionResolverTest extends TestCase
{
    public function test_version_resolver_returns_the_correct_version(): void
    {
        $resolver = new ManifestVersionResolver(__DIR__ . '/../Fixtures/public/.vite/manifest.json');

        static::assertSame(
            expected: '0fd434e97fe8f9df7938687c77e09a8a',
            actual: $resolver->resolve(new GenericContainer()),
        );
    }

    public function test_version_resolver_returns_empty_string_when_manifest_file_does_not_exist(): void
    {
        $resolver = new ManifestVersionResolver(__DIR__ . '/../Fixtures/public/.vite/missing-manifest.json');

        static::assertSame(
            expected: '',
            actual: $resolver->resolve(new GenericContainer()),
        );
    }
}
