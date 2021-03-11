<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CountryCode extends BaseRule
{
    public static function getAlias(): string
    {
        return 'country_code';
    }

    public function passes($attribute, $value, $parameters = [], $validator = null): bool
    {
        return validate_country_code($value);
    }
}
