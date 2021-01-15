<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Casts\CastableFromStringInterface;
use App\Casts\CastableToStringInterface;

abstract class StringValueObject implements CastableToStringInterface, CastableFromStringInterface
{
    protected $value;

    public function __construct(string $value)
    {
        $this->guard($value);

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return new static($value);
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    abstract protected function guard(string $value): void;
}
