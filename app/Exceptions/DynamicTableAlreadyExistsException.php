<?php

namespace App\Exceptions;

final class DynamicTableAlreadyExistsException extends DynamicApiException
{
    public function __construct(string $tableName)
    {
        parent::__construct("Dynamic table {$tableName} already exists.", 409);
    }
}
