<?php

namespace App\Repositories;

use App\Models\DynamicTableDefinition;
use Illuminate\Database\Eloquent\Collection;

final class DynamicTableDefinitionRepository
{
    public function findByName(string $tableName): ?DynamicTableDefinition
    {
        return DynamicTableDefinition::query()
            ->with('columns')
            ->where('table_name', $tableName)
            ->first();
    }

    public function existsByName(string $tableName): bool
    {
        return DynamicTableDefinition::query()
            ->where('table_name', $tableName)
            ->exists();
    }

    /** @return Collection<int, DynamicTableDefinition> */
    public function allWithColumnCounts(): Collection
    {
        return DynamicTableDefinition::query()
            ->withCount([
                'columns' => fn ($query) => $query->where('is_primary_key_internal', false),
            ])
            ->orderBy('table_name')
            ->get();
    }

    /** @param array<int, array<string, mixed>> $columns */
    public function createWithColumns(string $tableName, ?string $userFriendlyName, array $columns): DynamicTableDefinition
    {
        $definition = DynamicTableDefinition::query()->create([
            'table_name' => $tableName,
            'user_friendly_name' => $userFriendlyName,
        ]);

        foreach ($columns as $column) {
            $definition->columns()->create($column);
        }

        return $definition->fresh('columns');
    }
}
