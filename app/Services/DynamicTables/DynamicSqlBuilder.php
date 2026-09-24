<?php

namespace App\Services\DynamicTables;

final class DynamicSqlBuilder
{
    public function quoteIdentifier(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }

    /** @param array<int, array{name: string, postgresType: string, isNullable: bool}> $columns */
    public function createTableSql(string $tableName, array $columns): string
    {
        $driver = config('database.default');
        $idDefinition = $driver === 'sqlite'
            ? $this->quoteIdentifier('id').' INTEGER PRIMARY KEY AUTOINCREMENT'
            : $this->quoteIdentifier('id').' BIGSERIAL PRIMARY KEY';

        $definitions = [$idDefinition];

        foreach ($columns as $column) {
            $definitions[] = sprintf(
                '%s %s%s',
                $this->quoteIdentifier($column['name']),
                $column['postgresType'],
                $column['isNullable'] ? '' : ' NOT NULL'
            );
        }

        return sprintf(
            'CREATE TABLE %s (%s)',
            $this->quoteIdentifier($tableName),
            implode(', ', $definitions)
        );
    }

    /** @param array<int, string> $columns */
    public function insertSql(string $tableName, array $columns): string
    {
        if ($columns === []) {
            return sprintf('INSERT INTO %s DEFAULT VALUES', $this->quoteIdentifier($tableName));
        }

        $columnSql = implode(', ', array_map($this->quoteIdentifier(...), $columns));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        return sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->quoteIdentifier($tableName), $columnSql, $placeholders);
    }

    /** @param array<int, string> $columns */
    public function insertReturningIdSql(string $tableName, array $columns): string
    {
        return $this->insertSql($tableName, $columns).' RETURNING '.$this->quoteIdentifier('id');
    }

    /** @param array<int, string> $columns */
    public function updateSql(string $tableName, array $columns): string
    {
        $assignments = implode(', ', array_map(
            fn (string $column): string => $this->quoteIdentifier($column).' = ?',
            $columns
        ));

        return sprintf('UPDATE %s SET %s WHERE %s = ?', $this->quoteIdentifier($tableName), $assignments, $this->quoteIdentifier('id'));
    }

    public function selectByIdSql(string $tableName): string
    {
        return sprintf('SELECT * FROM %s WHERE %s = ? LIMIT 1', $this->quoteIdentifier($tableName), $this->quoteIdentifier('id'));
    }

    public function deleteSql(string $tableName): string
    {
        return sprintf('DELETE FROM %s WHERE %s = ?', $this->quoteIdentifier($tableName), $this->quoteIdentifier('id'));
    }

    public function listSql(string $tableName): string
    {
        return sprintf('SELECT * FROM %s ORDER BY %s ASC LIMIT ? OFFSET ?', $this->quoteIdentifier($tableName), $this->quoteIdentifier('id'));
    }

    public function countSql(string $tableName): string
    {
        return sprintf('SELECT COUNT(*) AS aggregate FROM %s', $this->quoteIdentifier($tableName));
    }
}
