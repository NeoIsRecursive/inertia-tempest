# Inertia tempest

> Extremely WIP, lots to implement and some stuff tempest lacks (like getting data from json bodies)

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
    versionResolver: ManifestVersionResolver::class,
    rootView: root_path('/views/app.view.php'),
    sharedPropsResolver: DefaultSharedPropResolver::class,
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

- [x] Resolve props correctly (Always, Lazy)
- [x] Run callables through container (is it possible?)
- [x] Fix error responses
- [x] Fix empty responses (will never be becuase a tempest route must return a response)
- [x] Implement 409 conflict responses when hash mismatch
- [ ] Improve rendering api?
- [ ] Create vite package?
- [ ] Json bodies on post.
- [ ] handle middleware better, self invoking 👀?
