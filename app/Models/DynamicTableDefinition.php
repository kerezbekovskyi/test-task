<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DynamicTableDefinition extends Model
{
    protected $table = 'app_dynamic_table_definitions';

    protected $fillable = [
        'table_name',
        'user_friendly_name',
    ];

    public function columns(): HasMany
    {
        return $this->hasMany(DynamicColumnDefinition::class, 'table_definition_id')
            ->orderBy('id');
    }
}
