<?php

declare(strict_types=1);

namespace App\ValueObjects;

use MyCLabs\Enum\Enum as BaseEnum;
use App\Casts\CastableFromStringInterface;
use App\Casts\CastableToStringInterface;

abstract class Enum extends BaseEnum implements CastableFromStringInterface, CastableToStringInterface, HasValidationRulesInterface
{
    use HasValidationRulesTrait;

    public static function fromString(string $value): self
    {
        return new static($value);
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public static function doGetValidationRules(): array
    {
        return [
            '' => 'in:' . implode(',', self::toArray()),
        ];
    }
}
