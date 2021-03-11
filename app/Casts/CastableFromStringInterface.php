<?php

declare(strict_types=1);

namespace App\Casts;

interface CastableFromStringInterface
{
    public static function fromString(string $value);
}
