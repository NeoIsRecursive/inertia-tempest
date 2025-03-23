<?php

declare(strict_types=1);

use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Support\ResolveErrorProps;
use Tempest\Auth\Authenticator;

use function Tempest\root_path;

return new InertiaConfig(
    rootView: root_path('/views/app.view.php'),
    sharedProps: [
        'user' => new AlwaysProp(fn(Authenticator $auth) => $auth->currentUser()),
        'errors' => new AlwaysProp(fn(ResolveErrorProps $errors) => $errors->resolve()),
    ],
);
