<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DynamicColumnDefinition extends Model
{
    protected $table = 'app_dynamic_column_definitions';

    protected $fillable = [
        'table_definition_id',
        'column_name',
        'column_type',
        'postgres_column_type',
        'is_nullable',
        'is_primary_key_internal',
    ];

    protected $casts = [
        'is_nullable' => 'boolean',
        'is_primary_key_internal' => 'boolean',
    ];

    public function tableDefinition(): BelongsTo
    {
        return $this->belongsTo(DynamicTableDefinition::class, 'table_definition_id');
    }
}
