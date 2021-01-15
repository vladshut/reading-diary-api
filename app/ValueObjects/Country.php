<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Utils\Assert;

final class Country extends StringValueObject
{
    protected function guard(string $value): void
    {
        Assert::countryCode($value);
    }
}
