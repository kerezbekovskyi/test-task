<?php

namespace Tests\Feature\DynamicTables;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicTableCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_schema_and_runs_full_crud_cycle(): void
    {
        $this->postJson('/api/v1/dynamic-tables/schemas', [
            'tableName' => 'contacts_alpha',
            'userFriendlyName' => 'Alpha contacts',
            'columns' => [
                ['name' => 'full_name', 'type' => 'TEXT', 'isNullable' => false],
                ['name' => 'email', 'type' => 'TEXT', 'isNullable' => true],
                ['name' => 'age', 'type' => 'INTEGER', 'isNullable' => true],
                ['name' => 'is_active', 'type' => 'BOOLEAN', 'isNullable' => false],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('tableName', 'contacts_alpha')
            ->assertJsonPath('columns.0.name', 'id')
            ->assertJsonPath('columns.1.name', 'full_name');

        $created = $this->postJson('/api/v1/dynamic-tables/data/contacts_alpha', [
            'full_name' => 'Ivan Petrov',
            'email' => 'ivan@example.com',
            'age' => 35,
            'is_active' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('full_name', 'Ivan Petrov')
            ->json();

        $id = $created['id'];

        $this->getJson("/api/v1/dynamic-tables/data/contacts_alpha/{$id}")
            ->assertOk()
            ->assertJsonPath('email', 'ivan@example.com');

        $this->putJson("/api/v1/dynamic-tables/data/contacts_alpha/{$id}", [
            'full_name' => 'Ivan Updated',
            'email' => 'updated@example.com',
            'age' => 36,
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('full_name', 'Ivan Updated');

        $this->getJson('/api/v1/dynamic-tables/data/contacts_alpha?page=0&size=10')
            ->assertOk()
            ->assertJsonPath('totalElements', 1)
            ->assertJsonPath('pageable.pageSize', 10);

        $this->deleteJson("/api/v1/dynamic-tables/data/contacts_alpha/{$id}")
            ->assertNoContent();

        $this->getJson("/api/v1/dynamic-tables/data/contacts_alpha/{$id}")
            ->assertNotFound();
    }

    public function test_schema_list_counts_only_user_defined_columns(): void
    {
        $this->postJson('/api/v1/dynamic-tables/schemas', [
            'tableName' => 'contacts_delta',
            'columns' => [
                ['name' => 'full_name', 'type' => 'TEXT'],
                ['name' => 'email', 'type' => 'TEXT'],
            ],
        ])->assertCreated();

        $this->getJson('/api/v1/dynamic-tables/schemas')
            ->assertOk()
            ->assertJsonPath('0.tableName', 'contacts_delta')
            ->assertJsonPath('0.columnCount', 2);
    }

    public function test_full_update_requires_nullable_fields_too(): void
    {
        $this->postJson('/api/v1/dynamic-tables/schemas', [
            'tableName' => 'contacts_epsilon',
            'columns' => [
                ['name' => 'full_name', 'type' => 'TEXT', 'isNullable' => false],
                ['name' => 'email', 'type' => 'TEXT', 'isNullable' => true],
            ],
        ])->assertCreated();

        $created = $this->postJson('/api/v1/dynamic-tables/data/contacts_epsilon', [
            'full_name' => 'Ivan',
        ])->assertCreated()->json();

        $this->putJson('/api/v1/dynamic-tables/data/contacts_epsilon/'.$created['id'], [
            'full_name' => 'Ivan Updated',
        ])
            ->assertBadRequest()
            ->assertJsonPath('status', 400);
    }
}
