<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Password extends BaseRule
{
    public static function getAlias(): string
    {
        return 'app_password';
    }

    public function passes($attribute, $value, $parameters = [], $validator = null): bool
    {
        $pattern = '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@()$%^&*=_{}[\]:;"\'|\\<>,.\/~`±§+-]).{8,30}$/u';

        return preg_match($pattern, $value) > 0;
    }
}
