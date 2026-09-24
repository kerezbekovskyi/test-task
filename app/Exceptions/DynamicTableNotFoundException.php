<?php

namespace App\Exceptions;

final class DynamicTableNotFoundException extends DynamicApiException
{
    public function __construct(string $tableName)
    {
        parent::__construct("Dynamic table {$tableName} was not found.", 404);
    }
}
