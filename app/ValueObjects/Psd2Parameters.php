<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Casts\CastableFromArrayInterface;
use App\Casts\CastableJsonTrait;
use App\Casts\CastableToArrayInterface;

final class Psd2Parameters implements CastableToArrayInterface, CastableFromArrayInterface, HasValidationRulesInterface
{
    use CastableJsonTrait;
    use HasValidationRulesTrait;

    private $amount;
    private $payee;

    protected static function doGetValidationRules(): array
    {
        return [
            'amount' => 'required|string',
            'payee' => 'required|string',
        ];
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getPayee()
    {
        return $this->payee;
    }
}
