<?php

namespace App\Exceptions;

final class InvalidDynamicRecordException extends DynamicApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 400);
    }
}
