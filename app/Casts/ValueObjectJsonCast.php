<?php

declare(strict_types=1);

namespace App\Casts;

use App\Utils\Assert;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

final class ValueObjectJsonCast implements CastsAttributes
{
    private $valueObjectClass;

    /**
     * ChannelConfigCast constructor.
     * @param string $valueObjectClass
     */
    public function __construct(string $valueObjectClass)
    {
        Assert::true(is_subclass_of($valueObjectClass, CastableToArrayInterface::class));
        Assert::true(is_subclass_of($valueObjectClass, CastableFromArrayInterface::class));
        $this->valueObjectClass = $valueObjectClass;
    }


    public function get($model, string $key, $value, array $attributes)
    {
        Assert::nullOrJson($value);

        if (!$value) {
            return null;
        }

        return call_user_func(
            $this->valueObjectClass . '::fromArray',
            json_decode($value, true, 512)
        );
    }

    public function set($model, string $key, $value, array $attributes)
    {
        Assert::nullOrIsInstanceOf($value, $this->valueObjectClass);

        return $value ? json_encode($value->toArray()) : null;
    }
}
