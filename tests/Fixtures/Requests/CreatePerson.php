<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Tests\Fixtures\Requests;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\Rules\IsBetween;
use Tempest\Validation\Rules\IsEmail;
use Tempest\Validation\Rules\IsNotEmptyString;
use Tempest\Validation\Rules\IsNumeric;

final class CreatePerson implements Request
{
    use IsRequest;

    #[IsNotEmptyString]
    public string $firstName;
    #[IsNotEmptyString]
    public string $lastName;
    #[IsNumeric, IsBetween(min: 0, max: 130)]
    public int $age;
    #[IsEmail]
    public ?string $email;
}
