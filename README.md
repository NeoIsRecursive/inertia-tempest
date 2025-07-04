# Inertia tempest

[![Coverage Status](https://coveralls.io/repos/github/NeoIsRecursive/inertia-tempest/badge.svg?branch=main)](https://coveralls.io/github/NeoIsRecursive/inertia-tempest?branch=main)

## installation

```bash
composer require neoisrecursive/inertia-tempest
```

## Setup

First you need to create a `InertiaConfig` in your apps Config directory.
Otherwise the default one will be used, which looks like this:

```php
<?php

use NeoIsRecursive\Inertia\DefaultSharedPropResolver;
use NeoIsRecursive\Inertia\InertiaConfig;
use NeoIsRecursive\Inertia\ManifestVersionResolver;

use function Tempest\root_path;

return new InertiaConfig(
    /**
     * The view that inertia should render on the first request
     */
    rootView: 'app.view.php',
    /**
     * Version resolver, if you use vite for example you probably want to use the default here,
     * or you can add a custom one to maybe get from enviroment variables etc.
     *
     * default path: public/build/manifest.json
     */
    versionResolver: new ManifestVersionResolver(),
    /**
     * Props that should be included in "all" requests, the default is errors and the authenticated user
     */
    sharedProps: [
        'user' => new AlwaysProp(fn(Authenticator $auth) => $auth->currentUser()),
        'errors' => new AlwaysProp(fn(ResolveErrorProps $errors) => $errors->resolve()),
    ]
);
```

The view will then be rendered as an `NeoIsRecursive\Inertia\Views\InertiaBaseView` and to render the inertia element you just do:

```php
<?= $this->renderInertiaElement(id: 'app') ?>
```

in your view, that will render a div with the page data (the id here should match the id you specified in your client setup).

See how to install inertia to your frontend on inertia's official site [here](https://inertiajs.com/client-side-setup).

When that is done you can start returning inertia responses from your tempest app!

```php
use Tempest\Http\Get;
use NeoIsRecursive\Inertia\InertiaResponse;

use function NeoIsRecursive\Inertia\inertia;

final class ReviewController
{
    #[Get(uri: '/review/{review}')]
    public function show(Review $review): InertiaResponse
    {
        return inertia('review/show', [
            'review' => $review,
        ]);
    }
}
```

## TODO

- [ ] Installer with js boilerplate?
- [ ] "Migrate" all tests from the laravel adapter.
- [ ] Add history encryption setting.
