<?php

use NeoIsRecursive\Inertia\InertiaConfig;

use function Tempest\root_path;

return new InertiaConfig(
    rootView: root_path('/views/app.view.php'),
);
