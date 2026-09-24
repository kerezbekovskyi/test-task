<?php

namespace App\Repositories;

use App\Services\DynamicTables\DynamicSqlBuilder;
use Illuminate\Support\Facades\DB;

final class DynamicRecordRepository
{
    public function __construct(private readonly DynamicSqlBuilder $sql) {}

    /** @param array<string, mixed> $values */
    public function insert(string $tableName, array $values): int|string
    {
        $columns = array_keys($values);

        $driver = config('database.default');
        if ($driver === 'pgsql') {
            $row = DB::selectOne($this->sql->insertReturningIdSql($tableName, $columns), array_values($values));

            return $row->id;
        }

        DB::insert($this->sql->insertSql($tableName, $columns), array_values($values));

        return DB::getPdo()->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function find(string $tableName, int|string $id): ?array
    {
        $row = DB::selectOne($this->sql->selectByIdSql($tableName), [$id]);

        return $row ? (array) $row : null;
    }

    /** @return array{content: array<int, array<string, mixed>>, total: int} */
    public function paginate(string $tableName, int $page, int $size): array
    {
        $totalRow = DB::selectOne($this->sql->countSql($tableName));
        $rows = DB::select($this->sql->listSql($tableName), [$size, $page * $size]);

        return [
            'content' => array_map(fn (object $row): array => (array) $row, $rows),
            'total' => (int) ($totalRow->aggregate ?? 0),
        ];
    }

    /** @param array<string, mixed> $values */
    public function update(string $tableName, int|string $id, array $values): void
    {
        DB::update($this->sql->updateSql($tableName, array_keys($values)), [...array_values($values), $id]);
    }

    public function delete(string $tableName, int|string $id): int
    {
        return DB::delete($this->sql->deleteSql($tableName), [$id]);
    }
}
