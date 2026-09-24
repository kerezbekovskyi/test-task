<?php

namespace App\Exceptions;

final class InvalidDynamicSchemaException extends DynamicApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 400);
    }
}
