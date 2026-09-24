<?php

namespace App\Services\DynamicTables;

final class DynamicTypeRegistry
{
    /** @var array<string, string> */
    private const TYPES = [
        'TEXT' => 'TEXT',
        'INTEGER' => 'INTEGER',
        'BIGINT' => 'BIGINT',
        'DECIMAL' => 'NUMERIC(19, 4)',
        'BOOLEAN' => 'BOOLEAN',
        'DATE' => 'DATE',
        'TIMESTAMP' => 'TIMESTAMP WITHOUT TIME ZONE',
    ];

    /** @return array<int, string> */
    public function supportedTypes(): array
    {
        return array_keys(self::TYPES);
    }

    public function postgresTypeFor(string $type): string
    {
        return self::TYPES[strtoupper($type)] ?? '';
    }

    public function isSupported(string $type): bool
    {
        return array_key_exists(strtoupper($type), self::TYPES);
    }
}
