<?php

namespace App\Http\Requests;

use App\Services\DynamicTables\DynamicTypeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDynamicTableSchemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var DynamicTypeRegistry $types */
        $types = app(DynamicTypeRegistry::class);

        return [
            'tableName' => ['required', 'string', 'min:3', 'max:63'],
            'userFriendlyName' => ['nullable', 'string', 'max:255'],
            'columns' => ['required', 'array', 'min:1'],
            'columns.*.name' => ['required', 'string', 'min:3', 'max:63'],
            'columns.*.type' => ['required', 'string', Rule::in($types->supportedTypes())],
            'columns.*.isNullable' => ['sometimes', 'boolean'],
        ];
    }
}
