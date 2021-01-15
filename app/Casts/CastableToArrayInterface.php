<?php

declare(strict_types=1);

namespace App\Casts;

interface CastableToArrayInterface
{
    public function toArray(): array;
}
