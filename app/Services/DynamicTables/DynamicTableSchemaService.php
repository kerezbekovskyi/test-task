<?php

namespace App\Services\DynamicTables;

use App\Exceptions\DynamicTableAlreadyExistsException;
use App\Exceptions\DynamicTableNotFoundException;
use App\Models\DynamicTableDefinition;
use App\Repositories\DynamicTableDefinitionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class DynamicTableSchemaService
{
    public function __construct(
        private readonly DynamicTableDefinitionRepository $tables,
        private readonly DynamicTableValidator $validator,
        private readonly DynamicTypeRegistry $types,
        private readonly DynamicSqlBuilder $sql,
    ) {}

    /** @param array{tableName: string, userFriendlyName?: string|null, columns: array<int, array<string, mixed>>} $payload */
    public function create(array $payload): DynamicTableDefinition
    {
        $tableName = $payload['tableName'];

        $this->validator->validateIdentifier($tableName, 'table name');
        $this->validator->validateSchemaColumns($payload['columns']);

        if ($this->tables->existsByName($tableName)) {
            throw new DynamicTableAlreadyExistsException($tableName);
        }

        return DB::transaction(function () use ($payload, $tableName): DynamicTableDefinition {
            $userColumns = collect($payload['columns'])
                ->map(fn (array $column): array => [
                    'name' => $column['name'],
                    'type' => strtoupper($column['type']),
                    'postgresType' => $this->types->postgresTypeFor(strtoupper($column['type'])),
                    'isNullable' => (bool) ($column['isNullable'] ?? true),
                ])
                ->values()
                ->all();

            DB::statement($this->sql->createTableSql($tableName, $userColumns));

            $metadataColumns = [[
                'column_name' => 'id',
                'column_type' => 'BIGINT',
                'postgres_column_type' => 'BIGINT',
                'is_nullable' => false,
                'is_primary_key_internal' => true,
            ]];

            foreach ($userColumns as $column) {
                $metadataColumns[] = [
                    'column_name' => $column['name'],
                    'column_type' => $column['type'],
                    'postgres_column_type' => $column['postgresType'],
                    'is_nullable' => $column['isNullable'],
                    'is_primary_key_internal' => false,
                ];
            }

            return $this->tables->createWithColumns(
                $tableName,
                $payload['userFriendlyName'] ?? null,
                $metadataColumns
            );
        });
    }

    public function get(string $tableName): DynamicTableDefinition
    {
        $this->validator->validateIdentifier($tableName, 'table name');

        return $this->tables->findByName($tableName)
            ?? throw new DynamicTableNotFoundException($tableName);
    }

    /** @return Collection<int, DynamicTableDefinition> */
    public function list(): Collection
    {
        return $this->tables->allWithColumnCounts();
    }
}
