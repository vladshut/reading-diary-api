<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

abstract class BaseRule implements Rule
{
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans(static::getAlias());
    }

    public function replacer($message, $attribute, $rule, $parameters, Validator $validator): string
    {
        return $message;
    }

    abstract public static function getAlias(): string;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param null $validator
     * @return bool
     */
    abstract public function passes($attribute, $value, $parameters = [], $validator = null): bool;
}
