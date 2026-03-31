<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Contracts;

use BackedEnum;
use DateInterval;
use DateTimeInterface;
use UnitEnum;

interface Onceable
{
    public function once(): static;

    public function shouldResolveOnce(): bool;

    public function fresh(): static;

    public function shouldBeRefreshed(): bool;

    public function as(BackedEnum|UnitEnum|string $key): static;

    public function getKey(): ?string;

    public function until(DateTimeInterface|DateInterval|int $delay): static;

    public function expiresAt(): ?int;
}
