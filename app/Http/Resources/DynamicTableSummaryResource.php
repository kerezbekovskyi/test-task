<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynamicTableSummaryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'tableName' => $this->table_name,
            'userFriendlyName' => $this->user_friendly_name,
            'columnCount' => $this->columns_count,
        ];
    }
}
