<?php

declare(strict_types=1);

namespace App\Casts;

use App\Utils\Assert;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

final class ValueObjectStringCast implements CastsAttributes
{
    private $valueObjectClass;

    /**
     * ChannelConfigCast constructor.
     * @param string $valueObjectClass
     */
    public function __construct(string $valueObjectClass)
    {
        Assert::true(is_subclass_of($valueObjectClass, CastableToStringInterface::class));
        Assert::true(is_subclass_of($valueObjectClass, CastableFromStringInterface::class));

        $this->valueObjectClass = $valueObjectClass;
    }


    public function get($model, string $key, $value, array $attributes)
    {
        Assert::nullOrString($value);

        if ($value === null) {
            return null;
        }

        return call_user_func($this->valueObjectClass . '::fromString', $value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        Assert::nullOrIsInstanceOf($value, $this->valueObjectClass);

        return $value ? $value->toString() : null;
    }
}
