<?php

namespace App\Services\DynamicTables;

use App\Exceptions\DynamicRecordNotFoundException;
use App\Exceptions\DynamicTableNotFoundException;
use App\Exceptions\InvalidDynamicRecordException;
use App\Models\DynamicColumnDefinition;
use App\Models\DynamicTableDefinition;
use App\Repositories\DynamicRecordRepository;
use App\Repositories\DynamicTableDefinitionRepository;
use Illuminate\Support\Collection;

final class DynamicTableDataService
{
    public function __construct(
        private readonly DynamicTableDefinitionRepository $tables,
        private readonly DynamicRecordRepository $records,
        private readonly DynamicTableValidator $validator,
        private readonly DynamicValueCaster $caster,
    ) {}

    /** @param array<string, mixed> $payload */
    public function create(string $tableName, array $payload): array
    {
        $definition = $this->schema($tableName);
        $values = $this->validatedValues($definition->columns, $payload, true);
        $id = $this->records->insert($tableName, $values);

        return $this->records->find($tableName, $id) ?? [];
    }

    /** @return array<string, mixed> */
    public function find(string $tableName, int|string $id): array
    {
        $this->schema($tableName);

        return $this->records->find($tableName, $id)
            ?? throw new DynamicRecordNotFoundException($tableName, $id);
    }

    /** @return array<string, mixed> */
    public function paginate(string $tableName, int $page, int $size): array
    {
        $this->schema($tableName);

        $result = $this->records->paginate($tableName, $page, $size);
        $totalPages = $result['total'] === 0 ? 0 : (int) ceil($result['total'] / $size);

        return [
            'content' => $result['content'],
            'pageable' => [
                'pageNumber' => $page,
                'pageSize' => $size,
            ],
            'totalPages' => $totalPages,
            'totalElements' => $result['total'],
            'last' => $totalPages === 0 || $page >= $totalPages - 1,
            'first' => $page === 0,
        ];
    }

    /** @param array<string, mixed> $payload */
    public function update(string $tableName, int|string $id, array $payload): array
    {
        $definition = $this->schema($tableName);

        if ($this->records->find($tableName, $id) === null) {
            throw new DynamicRecordNotFoundException($tableName, $id);
        }

        $values = $this->validatedValues($definition->columns, $payload, true, true);
        if ($values === []) {
            throw new InvalidDynamicRecordException('Update payload must contain at least one field.');
        }

        $this->records->update($tableName, $id, $values);

        return $this->find($tableName, $id);
    }

    public function delete(string $tableName, int|string $id): void
    {
        $this->schema($tableName);

        if ($this->records->delete($tableName, $id) === 0) {
            throw new DynamicRecordNotFoundException($tableName, $id);
        }
    }

    private function schema(string $tableName): DynamicTableDefinition
    {
        $this->validator->validateIdentifier($tableName, 'table name');

        return $this->tables->findByName($tableName)
            ?? throw new DynamicTableNotFoundException($tableName);
    }

    /**
     * @param  Collection<int, DynamicColumnDefinition>  $columns
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function validatedValues(Collection $columns, array $payload, bool $requireAllNotNullable, bool $requireAllColumns = false): array
    {
        $this->validator->validateRecordPayload($columns, $payload, $requireAllNotNullable, $requireAllColumns);

        $columnsByName = $columns
            ->reject(fn ($column) => $column->is_primary_key_internal)
            ->keyBy('column_name');

        $values = [];

        foreach ($payload as $field => $value) {
            $values[$field] = $this->caster->cast($value, $columnsByName->get($field));
        }

        return $values;
    }
}
