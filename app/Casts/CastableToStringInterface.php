<?php

declare(strict_types=1);

namespace App\Casts;

interface CastableToStringInterface
{
    public function toString(): string;
}
