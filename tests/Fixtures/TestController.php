<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Fixtures;

use NeoIsRecursive\Inertia\Http\Component;
use NeoIsRecursive\Inertia\Http\Middleware\EncryptHistory;
use NeoIsRecursive\Inertia\Inertia;
use NeoIsRecursive\Inertia\Props\AlwaysProp;
use NeoIsRecursive\Inertia\Props\OptionalProp;
use NeoIsRecursive\Inertia\Tests\Fixtures\Requests\CreatePerson;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Delete;
use Tempest\Router\Get;
use Tempest\Router\Patch;
use Tempest\Router\Post;
use Tempest\Router\Put;

use function Tempest\Router\uri;
use function Tempest\Support\arr;

final readonly class TestController
{
    #[Get(uri: '/')]
    public function index(): Component
    {
        return new Component(name: 'Index');
    }

    #[Get(uri: '/non-inertia-page')]
    public function nonInertiaPage(): Ok
    {
        return new Ok(body: [
            'message' => 'This is a non-Inertia page.',
        ]);
    }

    #[Get(uri: '/can-share-props-from-any-where')]
    public function testCanSharePropsFromAnyWhere(Inertia $inertia): Component
    {
        $inertia
            ->share(key: 'foo', value: 'bar')
            ->share([
                'baz' => 'qux',
            ]);

        return new Component(name: 'User/Edit');
    }

    #[Get(uri: '/all-sorts-of-props')]
    public function testAllSortsOfProps(Inertia $inertia): Component
    {
        $inertia->share(
            key: 'shared',
            value: Inertia::always(arr([1, 2, 3])),
        );

        return new Component(name: 'User/Edit', props: [
            'always' => Inertia::always(fn() => arr(['always-1', 'always-2'])),
            'optional' => Inertia::optional(fn() => arr(['optional-1', 'optional-2'])),
            'defer' => Inertia::defer(fn() => arr(['defer-1', 'defer-2'])),
        ]);
    }

    #[Get(uri: '/encrypted-history')]
    public function testEncryptedHistory(Inertia $inertia): Component
    {
        $inertia->encryptHistory();

        return new Component('User/Edit');
    }

    #[Get(uri: '/encrypted-history-middleware', middleware: [EncryptHistory::class])]
    public function testEncryptedHistoryWithMiddleware(Inertia $inertia): Component
    {
        return new Component('User/Edit');
    }

    #[Get(uri: '/cleared-history')]
    public function testClearedHistory(Inertia $inertia): Component
    {
        $inertia->clearHistory();

        return new Component('User/Edit');
    }

    #[Get(uri: '/redirect-with-clear-history')]
    public function testRedirectWithClearHistory(Inertia $inertia): Redirect
    {
        $inertia->clearHistory();
        return new Redirect(to: uri([self::class, 'testCanSharePropsFromAnyWhere']));
    }

    #[Delete(uri: '/test-post-with-redirect')]
    public function testPostWithRedirect(): Redirect
    {
        return new Redirect(to: uri([static::class, 'index']));
    }

    #[Patch(uri: '/test-patch-with-redirect')]
    public function testPatchWithRedirect(): Redirect
    {
        return new Redirect(to: uri([static::class, 'index']));
    }

    #[Put(uri: '/test-put-with-redirect')]
    public function testPutWithRedirect(): Redirect
    {
        return new Redirect(to: uri([static::class, 'index']));
    }

    #[Get(uri: '/test-can-merge-props')]
    public function testCanMergeProps(): Component
    {
        return new Component(name: 'test', props: [
            'foo' => new AlwaysProp(fn() => 'bar')->merge(),
            'baz' => new OptionalProp(fn() => 'qux')->merge(),
        ]);
    }

    #[Post(uri: '/test-validation-errors')]
    public function testValidationErrors(CreatePerson $request): Redirect
    {
        // If we reach here, the request is valid.
        return new Redirect(to: uri([static::class, 'index']));
    }
}
