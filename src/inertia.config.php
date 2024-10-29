<?php

use NeoIsRecursive\Inertia\DefaultSharedPropResolver;
use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\ManifestVersionResolver;

use function Tempest\root_path;

return new InertiaConfig(
    versionResolver: ManifestVersionResolver::class,
    rootView: root_path('/views/app.view.php'),
    sharedPropsResolver: DefaultSharedPropResolver::class,
);
