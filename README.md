# Inertia tempest

> Extremely WIP, lots to implement and some stuff tempest lacks (like getting data from json bodies)

## installation

Not setup yet, you'll have to clone it and stuff.

## Setup

First you need to create a `InertiaConfig` in your apps Config directory.

````php
<?php

use NeoIsRecursive\Inertia\InertiaConfig;
use Tempest\Http\Session\Session;

use function Tempest\get;

return new InertiaConfig(
    version: '1.0.0',
    rootView: __DIR__ . '/../../views/app.view.php',
    getSharedProps: function () {
        return [
            'auth' => [
                'user' => get(Session::class)->get('user'),
            ],
        ];
    },
);
```

The view will then be rendered as an `NeoIsRecursive\Inertia\Views\InertiaBaseView` and to render the inertia element you just do:

```php
    <?= $this->renderInertiaElement(id: 'app') ?>
```

in your view, that will render a div with the page data (the id here should match the id you specified in your client setup).


See how to install inertia to your frontend on inertia's official site [here]("https://inertiajs.com/client-side-setup").

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

- [ ] Resolve props correctly (Always, Lazy)
- [ ] Run callables through container (is it possible?)
- [ ] Fix error responses
- [ ] Fix empty responses
- [ ] Implement 409 conflict responses when hash mismatch
- [ ] Improve rendering api?


````
