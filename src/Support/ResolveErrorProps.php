<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Support;

use Tempest\Http\Session\Session;
use Tempest\Validation\Rule;
use Tempest\Validation\Validator;

use function Tempest\Support\arr;

final readonly class ResolveErrorProps
{
    public function __construct(
        private Session $session,
        private Validator $validator,
    ) {}

    public function resolve(): array
    {
        /** @var Rule[][] */
        $failingRules = $this->session->get(Session::VALIDATION_ERRORS) ?? [];

        return arr($failingRules)->map(
            fn(array $rules): array => arr($rules)->map(
                // @mago-expect lint:prefer-first-class-callable
                fn(Rule $rule): string => $this->validator->getErrorMessage($rule),
            )->toArray(),
        )->toArray();
    }
}
