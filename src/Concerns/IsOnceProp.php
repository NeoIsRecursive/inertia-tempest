<?php

declare(strict_types=1);

namespace NeoIsRecursive\Inertia\Concerns;

use BackedEnum;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use UnitEnum;

trait IsOnceProp
{
    protected bool $resolveOnce = false;
    protected bool $refreshOnce = false;
    protected ?string $onceKey = null;
    protected ?int $onceExpiresAt = null;

    public function once(): static
    {
        return clone($this, ['resolveOnce' => true]);
    }

    public function shouldResolveOnce(): bool
    {
        return $this->resolveOnce;
    }

    public function fresh(): static
    {
        return clone($this, ['refreshOnce' => true]);
    }

    public function shouldBeRefreshed(): bool
    {
        return $this->refreshOnce;
    }

    public function as(BackedEnum|UnitEnum|string $key): static
    {
        return clone($this, ['onceKey' => match (true) {
            $key instanceof BackedEnum => (string) $key->value,
            $key instanceof UnitEnum => $key->name,
            default => $key,
        }]);
    }

    public function getKey(): ?string
    {
        return $this->onceKey;
    }

    public function until(DateTimeInterface|DateInterval|int $delay): static
    {
        $expiresAt = match (true) {
            $delay instanceof DateTimeInterface => DateTimeImmutable::createFromInterface($delay),
            $delay instanceof DateInterval => new DateTimeImmutable()->add($delay),
            default => new DateTimeImmutable()->modify(sprintf('+%d seconds', $delay)),
        };

        return clone($this, ['onceExpiresAt' => (int) $expiresAt->format('Uv')]);
    }

    public function expiresAt(): ?int
    {
        return $this->onceExpiresAt;
    }
}
