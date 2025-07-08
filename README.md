# Inertia tempest

[![Coverage Status](https://coveralls.io/repos/github/NeoIsRecursive/inertia-tempest/badge.svg?branch=main)](https://coveralls.io/github/NeoIsRecursive/inertia-tempest?branch=main)

## installation

```bash
composer require neoisrecursive/inertia-tempest
```

## Setup

It should be very familiar if you have used inertia before, with some small differences, for example:

- No configuration is done in the middleware, use the the config file, a provider class or your own middleware to set your "globally-shared-props".
- No automatic redirect back handling in non get requests, since all tempest routes must return a response you have to return your own `Back` response.
- No `Inertia::defer/lazy/always` method, in favor of just using the prop classes (`new DeferProp/LazyProp/AlwaysProp`) (unless people like that syntax more?)

### Backend

If you use the default config, a view called `app.view.php` is required. That view will then be rendered as an `NeoIsRecursive\Inertia\Views\InertiaBaseView` and to render the inertia element you just do:
(think of this like laravels `@inertia` directive)

```php
<?= $this->renderInertiaElement(id: 'app') ?>
```

### Frontend

Install the bundler of your choice, since tempest comes with a vite installer, that is very much recommended. [Tempest vite docs](https://tempestphp.com/1.x/features/asset-bundling).

When that is done, you can go on to install your prefered inertia client by following the guide on inertia's official site [here](https://inertiajs.com/client-side-setup)

### Usage

It is pretty similar to the laravel adapter, except that the `inertia` function is a namespaced function.

```php
use Tempest\Http\Get;
use NeoIsRecursive\Inertia\InertiaResponse;
use NeoIsRecursive\Inertia\Inertia;

use function NeoIsRecursive\Inertia\inertia;

final class ReviewController
{
    // Using the inertia helper function
    #[Get(uri: '/reviews/{review}')]
    public function show(Review $review): InertiaResponse
    {
        return inertia('reviews/show', [
            'review' => $review,
        ]);
    }

    // Using dependency injection
    #[Get(uri: '/reviews')]
    public function show(Inertia $inertia): InertiaResponse
    {
        return $inertia->render('reviews/index', [
            'reviews' => Review::all(),
        ]);
    }
}
```

## Configuration

This is the default config:

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

## TODO

- [ ] Installer?
- [ ] "Migrate" all tests from the laravel adapter.
- [x] Add history encryption setting.
