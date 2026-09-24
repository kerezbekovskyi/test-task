<?php

namespace App\Services\DynamicTables;

use App\Exceptions\InvalidDynamicRecordException;
use App\Exceptions\InvalidDynamicSchemaException;
use App\Models\DynamicColumnDefinition;
use Illuminate\Support\Collection;

final class DynamicTableValidator
{
    private const IDENTIFIER_PATTERN = '/^[a-z][a-z0-9_]{2,62}$/';

    /** @var array<int, string> */
    private const RESERVED_IDENTIFIERS = [
        'select', 'insert', 'update', 'delete', 'drop', 'create', 'alter',
        'table', 'where', 'from', 'order', 'limit', 'offset', 'group', 'by',
        'user', 'index', 'constraint', 'primary', 'foreign',
    ];

    public function __construct(private readonly DynamicTypeRegistry $types) {}

    public function validateIdentifier(string $identifier, string $field): void
    {
        if (! preg_match(self::IDENTIFIER_PATTERN, $identifier)) {
            throw new InvalidDynamicSchemaException("Invalid {$field}: use 3-63 lowercase latin letters, digits and underscores, starting with a letter.");
        }

        if (str_starts_with($identifier, 'pg_') || str_starts_with($identifier, 'app_')) {
            throw new InvalidDynamicSchemaException("Invalid {$field}: prefixes pg_ and app_ are reserved.");
        }

        if (in_array($identifier, self::RESERVED_IDENTIFIERS, true)) {
            throw new InvalidDynamicSchemaException("Invalid {$field}: reserved SQL keyword is not allowed.");
        }
    }

    /** @param array<int, array<string, mixed>> $columns */
    public function validateSchemaColumns(array $columns): void
    {
        if ($columns === []) {
            throw new InvalidDynamicSchemaException('Columns array must not be empty.');
        }

        $seen = [];

        foreach ($columns as $column) {
            $name = (string) ($column['name'] ?? '');
            $type = strtoupper((string) ($column['type'] ?? ''));

            $this->validateIdentifier($name, 'column name');

            if ($name === 'id') {
                throw new InvalidDynamicSchemaException('Column name id is reserved for internal primary key.');
            }

            if (isset($seen[$name])) {
                throw new InvalidDynamicSchemaException("Duplicate column name: {$name}.");
            }

            if (! $this->types->isSupported($type)) {
                throw new InvalidDynamicSchemaException("Unsupported column type: {$type}.");
            }

            $seen[$name] = true;
        }
    }

    /**
     * @param  Collection<int, DynamicColumnDefinition>  $columns
     * @param  array<string, mixed>  $payload
     */
    public function validateRecordPayload(Collection $columns, array $payload, bool $requireAllNotNullable, bool $requireAllColumns = false): void
    {
        if (array_key_exists('id', $payload)) {
            throw new InvalidDynamicRecordException('Column id cannot be provided manually.');
        }

        $allowed = $columns
            ->reject(fn ($column) => $column->is_primary_key_internal)
            ->keyBy('column_name');

        foreach (array_keys($payload) as $field) {
            if (! $allowed->has($field)) {
                throw new InvalidDynamicRecordException("Unknown field: {$field}.");
            }
        }

        if ($requireAllColumns) {
            foreach ($allowed as $column) {
                if (! array_key_exists($column->column_name, $payload)) {
                    throw new InvalidDynamicRecordException("Field {$column->column_name} is required for full update.");
                }
            }
        }

        if (! $requireAllNotNullable) {
            return;
        }

        foreach ($allowed as $column) {
            if (! $column->is_nullable && (! array_key_exists($column->column_name, $payload) || $payload[$column->column_name] === null)) {
                throw new InvalidDynamicRecordException("Field {$column->column_name} is required.");
            }
        }
    }
}
