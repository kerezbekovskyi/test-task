<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynamicTableSchemaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'tableName' => $this->table_name,
            'userFriendlyName' => $this->user_friendly_name,
            'columns' => $this->columns->map(fn ($column): array => [
                'name' => $column->column_name,
                'type' => $column->column_type,
                'postgresType' => $column->postgres_column_type,
                'isNullable' => $column->is_nullable,
                'isPrimaryKey' => $column->is_primary_key_internal,
            ])->values(),
        ];
    }
}
