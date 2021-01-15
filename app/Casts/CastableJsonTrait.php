<?php

declare(strict_types=1);

namespace App\Casts;

use App\Utils\Assert;

trait CastableJsonTrait
{
    protected function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public static function fromArray(array $data): self
    {
        Assert::arrayIsValid($data, self::getValidationRules());

        return new self($data);
    }

    public function toArray(): array
    {
        $result = get_object_vars($this);

        foreach ($result as $key => $value) {
            if ($value instanceof CastableToArrayInterface) {
                $result[$key] = $value->toArray();
            }
        }

        return $result;
    }
}
