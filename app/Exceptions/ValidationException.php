<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $messages = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $messages, $code = 0, Exception $previous = null)
    {
        $this->messages = $messages;

        parent::__construct(json_encode($messages), $code, $previous);
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
