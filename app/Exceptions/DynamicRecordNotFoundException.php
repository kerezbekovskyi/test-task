<?php

namespace App\Exceptions;

final class DynamicRecordNotFoundException extends DynamicApiException
{
    public function __construct(string $tableName, int|string $id)
    {
        parent::__construct("Record {$id} was not found in dynamic table {$tableName}.", 404);
    }
}
