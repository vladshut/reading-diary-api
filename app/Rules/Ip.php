<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Ip extends BaseRule
{
    public static function getAlias(): string
    {
        return 'ip';
    }

    public function passes($attribute, $value, $parameters = [], $validator = null): bool
    {
        return validate_ip($value);
    }
}
