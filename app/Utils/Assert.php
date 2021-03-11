<?php

declare(strict_types=1);

namespace App\Utils;

use Illuminate\Support\Facades\Validator;
use function sprintf;

/**
 * @method static nullOrJson($value, $message = '')
 */
final class Assert extends \Webmozart\Assert\Assert
{
    /**
     * @psalm-assert string $value
     *
     * @param mixed  $value
     * @param string $message
     */
    public static function phoneE164($value, $message = ''): void
    {
        $message = sprintf($message ?: 'Expected phone in E14. Got: %s %s', static::typeToString($value), (string)$value);

        self::true(validate_phone_e14($value), $message);
    }

    /**
     * @psalm-assert string $value
     *
     * @param mixed  $value
     * @param string $message
     */
    public static function ip($value, $message = ''): void
    {
        if (false === validate_ip($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to be an IP. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * @param $value
     * @param array $rules
     * @param string $message
     */
    public static function arrayIsValid($value, array $rules, $message = ''): void
    {
        self::isArray($value);
        $errors = Validator::make($value, $rules)->errors()->toArray();

        $valueStr = json_encode($value) ?: self::typeToString($value);
        $rulesStr = json_encode($rules) ?: self::typeToString($rules);
        $errorsStr = json_encode($errors) ?: self::typeToString($errors);

        $message = sprintf($message ?: "Value '%s' does not meet rules '%s'. Errors '%s'.", $valueStr, $rulesStr, $errorsStr);

        self::isEmpty($errors, $message);
    }

    public static function json($value, $message = ''): void
    {
        $message = sprintf($message ?: 'Expected value %s %s should be a valid json.', static::typeToString($value), (string)$value);

        self::true(is_json($value), $message);
    }
}
