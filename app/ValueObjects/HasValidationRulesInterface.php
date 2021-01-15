<?php

declare(strict_types=1);

namespace App\ValueObjects;

interface HasValidationRulesInterface
{
    public static function getValidationRules(string $prefix = null, array $extra = []): array;
}
