<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Utils\Assert;

final class Phone extends StringValueObject
{
    protected function guard(string $value): void
    {
        Assert::phoneE164($value);
    }
}
