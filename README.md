# Inertia tempest

[![Coverage Status](https://coveralls.io/repos/github/NeoIsRecursive/inertia-tempest/badge.svg?branch=main)](https://coveralls.io/github/NeoIsRecursive/inertia-tempest?branch=main)

> Note, most things work but redirects and session reflashing is still missing and all forms must use the `forceFormData: true` option.

## installation

Not setup yet, you'll have to clone it and stuff.

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
    rootView: root_path('/views/app.view.php'),
    /**
     * Version resolver, if you use vite for example you probably want to use the default here,
     * or you can add a custom one to maybe get from enviroment variables etc.
     */
    versionResolverClass: ManifestVersionResolver::class,
    /**
     * Props that should be included in "all" requests, the default is errors and the authenticated user
     */
    defaultPropsResolverClass: DefaultSharedPropResolver::class,
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
- [ ] Migrate tests from the laravel adapter.
- [ ] Create vite package?
- [ ] Json bodies on post.
