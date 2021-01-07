<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneE164 extends BaseRule
{
    public static function getAlias(): string
    {
        return 'phone_e164';
    }

    public function passes($attribute, $value, $parameters = [], $validator = null): bool
    {
        return validate_phone_e14($value);
    }
}
