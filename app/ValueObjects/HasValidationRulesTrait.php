<?php

declare(strict_types=1);

namespace App\ValueObjects;

trait HasValidationRulesTrait
{
    protected static function applyPrefixToValidationRules(?string $prefix, array $rules): array
    {
        if ($prefix) {
            foreach ($rules as $key => $value) {
                if ($key) {
                    $rules["{$prefix}.{$key}"] = $value;
                } else {
                    $rules[$prefix] = $value;
                }

                unset($rules[$key]);
            }
        }

        return $rules;
    }


    public static function getValidationRules(string $prefix = null, array $extra = [], array $context = []): array
    {

        $rules = static::doGetValidationRules($context);
        $rules = self::mergeRules($rules, $extra);

        return static::applyPrefixToValidationRules($prefix, $rules);
    }

    private static function rulesToArray(array $rules): array
    {
        foreach ($rules as $key => $fieldRules) {
            if (is_string($fieldRules)) {
                $fieldRules = explode('|', $fieldRules);
            }

            foreach ($fieldRules as $fieldRuleKey => $fieldRule) {
                $pieces = explode(':', $fieldRule);
                $rule = $pieces[0];
                $parametersStr = $pieces[1] ?? '';
                $fieldRules[$rule] = $parametersStr;
                unset($fieldRules[$fieldRuleKey]);
            }

            $rules[$key] = $fieldRules;
        }

        return $rules;
    }

    private static function arrayToRules(array $rulesArray): array
    {
        $rules = [];
        foreach ($rulesArray as $key => $fieldRules) {
            if (is_array($fieldRules)) {
                $fieldRulesStr = [];
                foreach ($fieldRules as $fieldRuleKey => $parametersStr) {
                    $fieldRuleStr = $fieldRuleKey;
                    if (!empty($parametersStr)) {
                        $fieldRuleStr .= ":{$parametersStr}";
                    }
                    $fieldRulesStr[] = $fieldRuleStr;
                }

                $rules[$key] = $fieldRulesStr;
            }
        }

        return $rules;
    }

    protected static function mergeRules(array $r1, array $r2): array
    {
        $r1 = self::rulesToArray($r1);
        $r2 = self::rulesToArray($r2);

        foreach ($r2 as $fieldName => $fieldRules) {
            if ($fieldRules === null) {
                unset($r1[$fieldName], $r2[$fieldName]);
                continue;
            }

            foreach ($fieldRules as $fieldRuleName => $parameters) {
                if ($fieldRuleName === null) {
                    unset($r1[$fieldName][$fieldRuleName], $r2[$fieldName][$fieldRuleName]);
                    continue;
                }

                $r1[$fieldName][$fieldRuleName] = $parameters;
                unset($r2[$fieldName][$fieldRuleName]);
            }
        }

        $rules = array_merge_recursive($r1, $r2);
        $rules = self::arrayToRules($rules);

        return $rules;
    }

    abstract protected static function doGetValidationRules(array $context = []): array;
}
