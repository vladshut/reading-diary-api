<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class LocaleIcu extends BaseRule
{
    public static function getAlias(): string
    {
        return 'locale_icu';
    }

    public function passes($attribute, $value, $parameters = [], $validator = null): bool
    {
        if (!is_string($value)) {
            return true;
        }

        return validate_locale_icu($value);
    }
}
