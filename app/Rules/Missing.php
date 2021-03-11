<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Validator;

class Missing extends BaseRule
{
    public static function getAlias(): string
    {
        return 'missing';
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param null $validator
     * @return bool
     */
    public function passes($attribute, $value, $parameters = [], $validator = null): bool
    {
        return false;
    }

    public function replacer($message, $attribute, $rule, $parameters, Validator $validator): string
    {
        return str_replace([':reason'], [$parameters[0] ?? ''], $message);
    }
}
