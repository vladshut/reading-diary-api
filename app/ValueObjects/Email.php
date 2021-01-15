<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Utils\Assert;

final class Email extends StringValueObject
{
    protected $value;

    public function __construct(string $value)
    {
        parent::__construct(strtolower(trim($value)));
    }
    /**
     * Returns the local part of the email address
     *
     * @return string
     */
    public function getLocalPart(): string
    {
        return explode('@', $this->value)[0];
    }

    /**
     * Returns the domain part of the email address
     *
     * @return string
     */
    public function getDomainPart(): string
    {
        return trim(explode('@', $this->value)[1], '[]');
    }

    protected function guard(string $value): void
    {
        Assert::email($value);
    }
}
