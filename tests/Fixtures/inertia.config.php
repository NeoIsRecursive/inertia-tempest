<?php

use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Support\ResolveErrorProps;
use NeoIsRecursive\Inertia\Tests\Fixtures\TestVersionResolver;
use Tempest\Auth\Authentication\Authenticator;

return new InertiaConfig(
    rootView: __DIR__ . '/../Fixtures/root.view.php',
    versionResolver: new TestVersionResolver(),
    sharedProps: [
        'user' => new AlwaysProp(fn(Authenticator $auth) => $auth->current()),
        'errors' => new AlwaysProp(fn(ResolveErrorProps $errors) => $errors->resolve()),
    ],
);
