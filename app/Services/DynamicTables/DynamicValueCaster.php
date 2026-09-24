<?php

namespace App\Services\DynamicTables;

use App\Exceptions\InvalidDynamicRecordException;
use App\Models\DynamicColumnDefinition;
use Carbon\CarbonImmutable;

final class DynamicValueCaster
{
    public function cast(mixed $value, DynamicColumnDefinition $column): mixed
    {
        if ($value === null) {
            if (! $column->is_nullable) {
                throw new InvalidDynamicRecordException("Field {$column->column_name} cannot be null.");
            }

            return null;
        }

        return match ($column->column_type) {
            'TEXT' => $this->castText($value, $column->column_name),
            'INTEGER' => $this->castInteger($value, $column->column_name),
            'BIGINT' => $this->castBigint($value, $column->column_name),
            'DECIMAL' => $this->castDecimal($value, $column->column_name),
            'BOOLEAN' => $this->castBoolean($value, $column->column_name),
            'DATE' => $this->castDate($value, $column->column_name),
            'TIMESTAMP' => $this->castTimestamp($value, $column->column_name),
            default => throw new InvalidDynamicRecordException("Unsupported field type for {$column->column_name}."),
        };
    }

    private function castText(mixed $value, string $field): string
    {
        if (is_array($value) || is_object($value)) {
            throw new InvalidDynamicRecordException("Field {$field} must be a scalar text value.");
        }

        return (string) $value;
    }

    private function castInteger(mixed $value, string $field): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', $value)) {
            return (int) $value;
        }

        throw new InvalidDynamicRecordException("Field {$field} must be an integer.");
    }

    private function castBigint(mixed $value, string $field): string|int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', $value)) {
            return $value;
        }

        throw new InvalidDynamicRecordException("Field {$field} must be a bigint.");
    }

    private function castDecimal(mixed $value, string $field): string
    {
        if ((is_int($value) || is_float($value) || is_string($value)) && is_numeric($value)) {
            return number_format((float) $value, 4, '.', '');
        }

        throw new InvalidDynamicRecordException("Field {$field} must be a decimal number.");
    }

    private function castBoolean(mixed $value, string $field): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === 0 || $value === 1 || $value === '0' || $value === '1') {
            return (bool) $value;
        }

        throw new InvalidDynamicRecordException("Field {$field} must be a boolean.");
    }

    private function castDate(mixed $value, string $field): string
    {
        if (! is_string($value)) {
            throw new InvalidDynamicRecordException("Field {$field} must be a date string.");
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value)->format('Y-m-d');
        } catch (\Throwable) {
            throw new InvalidDynamicRecordException("Field {$field} must use Y-m-d format.");
        }
    }

    private function castTimestamp(mixed $value, string $field): string
    {
        if (! is_string($value)) {
            throw new InvalidDynamicRecordException("Field {$field} must be a timestamp string.");
        }

        try {
            return CarbonImmutable::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            throw new InvalidDynamicRecordException("Field {$field} must be a valid timestamp.");
        }
    }
}
