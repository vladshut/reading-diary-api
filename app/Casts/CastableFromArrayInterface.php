<?php

declare(strict_types=1);

namespace App\Casts;

interface CastableFromArrayInterface
{
    public static function fromArray(array $data);
}
