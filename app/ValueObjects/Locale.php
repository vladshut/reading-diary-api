<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Utils\Assert;

final class Locale extends StringValueObject
{
    protected function guard(string $value): void
    {
        Assert::localeIcu($value);
    }
}
