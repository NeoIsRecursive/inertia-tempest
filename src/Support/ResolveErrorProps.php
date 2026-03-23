<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Support;

use Tempest\Http\Session\FormSession;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Validator;

use function Tempest\Support\arr;

final readonly class ResolveErrorProps
{
    public function __construct(
        private FormSession $session,
        private Validator $validator,
    ) {}

    public function resolve(): array
    {
        $failingRules = $this->session->getErrors();

        return arr($failingRules)->map(
            fn(array $rules): array => arr($rules)->map(
                fn(FailingRule $rule): string => $this->validator->getErrorMessage($rule),
            )->toArray(),
        )->toArray();
    }
}
