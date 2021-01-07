<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Webmozart\Assert\Assert;

class ContainsSubstring extends BaseRule
{
    public static function getAlias(): string
    {
        return 'contains_substring';
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
        if (!is_string($value)) {
            return true;
        }

        Assert::keyExists($parameters, 0);
        Assert::string($parameters[0]);
        if (isset($parameters[1])) {
            Assert::numeric($parameters[1]);
        }

        $substring = $parameters[0];
        $count = $parameters[1] ?? null;

        if ($count === null) {
            return strpos($value, $substring) !== false;
        }

        return substr_count($value, $substring) === (int)$count;
    }

    public function replacer($message, $attribute, $rule, $parameters, Validator $validator): string
    {
        $messageTemplateName = 'exact_times';

        if (!isset($attribute[1])) {
            $messageTemplateName = 'at_least_one';
        } elseif ($attribute[1] === 0) {
            $messageTemplateName = 'zero_times';
        }

        $messageTemplate = $message[$messageTemplateName];

        return str_replace([':substring', ':count'], [$parameters[0], $parameters[1] ?? ''], $messageTemplate);
    }
}
