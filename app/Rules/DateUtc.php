<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DateUtc extends BaseRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    public static function getAlias(): string
    {
        return 'date_utc';
    }

    public function passes($attribute, $value, $parameters = [], $validator = null): bool
    {
        if (!$value) {
            return true;
        }

        return validate_date_utc_format($value);
    }
}
