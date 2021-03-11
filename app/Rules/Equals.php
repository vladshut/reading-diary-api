<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Validator;
use Webmozart\Assert\Assert;

class Equals extends BaseRule
{
    public static function getAlias(): string
    {
        return 'equals';
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
        Assert::keyExists($parameters, 0);

        $expected = $parameters[0];

        return $value == $expected;
    }

    public function replacer($message, $attribute, $rule, $parameters, Validator $validator): string
    {
        return str_replace([':expected'], [$parameters[0]], $message);
    }
}
