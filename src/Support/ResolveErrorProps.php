<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Support;

use NeoIsRecursive\Inertia\Props\AlwaysProp;
use Tempest\Http\Session\Session;
use Tempest\Validation\Rule;

final readonly class ResolveErrorProps
{
    public function __construct(private Session $session) {}

    public function resolve(): array
    {
        return [
            'errors' => new AlwaysProp(fn() => array_map(
                fn(array $rules) => array_map(
                    fn(Rule $rule) => $rule->message(),
                    $rules
                ),
                $this->session->consume(Session::VALIDATION_ERRORS) ?? []
            )),
        ];
    }
}
