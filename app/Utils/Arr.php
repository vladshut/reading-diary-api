<?php

declare(strict_types=1);

namespace App\Utils;

final class Arr extends \Illuminate\Support\Arr
{
    /**
     * @param iterable $array
     * @param string $char
     * @param string $prepend
     * @return array
     */
    public static function flattenWith($array, string $char, $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::flattenWith($value, $char, $prepend . $key . $char));
            } else {
                if (is_int($key)) {
                    break;
                }
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * @param iterable $array
     * @return array
     */
    public static function whereJson($array): array
    {
        return static::flattenWith($array, '->');
    }

    /**
     * @param array $array
     * @param string|null $prefix
     * @return array
     */
    public static function addPrefix(array $array, ?string $prefix): array
    {
        if ($prefix) {
            foreach ($array as $key => $value) {
                $array["{$prefix}{$key}"] = $value;
                unset($array[$key]);
            }
        }

        return $array;
    }

    public static function plainDot($array, $prepend = ''): array
    {
        $result = parent::dot($array, $prepend);

        foreach ($result as $key => $value) {
            if (is_array($value)) {
                $result[$key] = json_encode($value);
            }
        }

        return $result;
    }
}
