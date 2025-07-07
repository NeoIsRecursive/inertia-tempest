<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Fixtures;

use NeoIsRecursive\Inertia\Http\InertiaResponse;
use NeoIsRecursive\Inertia\Http\Middleware\EncryptHistory;
use NeoIsRecursive\Inertia\Inertia;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;
use Tempest\Router\Get;

use function NeoIsRecursive\Inertia\inertia;
use function Tempest\uri;

final readonly class TestController
{
    public function index(): InertiaResponse
    {
        return inertia(component: 'Index');
    }

    #[Get(uri: '/non-inertia-page')]
    public function nonInertiaPage(): Ok
    {
        return new Ok(body: [
            'message' => 'This is a non-Inertia page.',
        ]);
    }

    #[Get(uri: '/can-share-props-from-any-where')]
    public function testCanSharePropsFromAnyWhere(Inertia $inertia): InertiaResponse
    {
        $inertia
            ->share(
                key: 'foo',
                value: 'bar',
            )
            ->share([
                'baz' => 'qux',
            ]);

        return inertia(component: 'User/Edit');
    }

    #[Get(uri: '/all-sorts-of-props')]
    public function testAllSortsOfProps(Inertia $inertia): InertiaResponse
    {
        return $inertia->share(
            key: 'foo',
            value: 'bar',
        )->render(
            component: 'User/Edit',
            props: [
                new AlwaysProp(fn() => 'baz'),
            ],
        );
    }

    #[Get(uri: '/encrypted-history')]
    public function testEncryptedHistory(Inertia $inertia): InertiaResponse
    {
        return $inertia->encryptHistory()->render(component: 'User/Edit');
    }

    #[Get(uri: '/encrypted-history-middleware', middleware: [EncryptHistory::class])]
    public function testEncryptedHistoryWithMiddleware(Inertia $inertia): InertiaResponse
    {
        return $inertia->render(component: 'User/Edit');
    }

    #[Get(uri: '/cleared-history')]
    public function testClearedHistory(Inertia $inertia): InertiaResponse
    {
        return $inertia->clearHistory()->render(component: 'User/Edit');
    }

    #[Get(uri: '/redirect-with-clear-history')]
    public function testRedirectWithClearHistory(Inertia $inertia): Redirect
    {
        $inertia->clearHistory();
        return new Redirect(to: uri([self::class, 'testCanSharePropsFromAnyWhere']));
    }
}
