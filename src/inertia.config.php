<?php

declare(strict_types=1);

use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Support\ResolveErrorProps;
use Tempest\Auth\Authentication\Authenticator;

return new InertiaConfig(
    rootView: 'app.view.php',
    sharedProps: [
        'user' => new AlwaysProp(fn(Authenticator $auth) => $auth->current()),
        'errors' => new AlwaysProp(fn(ResolveErrorProps $errors) => $errors->resolve()),
    ],
);
