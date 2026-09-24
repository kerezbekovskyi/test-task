<?php

namespace Tests\Feature\DynamicTables;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicTableSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_injection_like_table_names(): void
    {
        $this->postJson('/api/v1/dynamic-tables/schemas', [
            'tableName' => 'users;drop',
            'columns' => [
                ['name' => 'full_name', 'type' => 'TEXT'],
            ],
        ])
            ->assertBadRequest()
            ->assertJsonPath('status', 400);
    }

    public function test_it_rejects_unknown_record_fields_and_missing_required_fields(): void
    {
        $this->postJson('/api/v1/dynamic-tables/schemas', [
            'tableName' => 'contacts_beta',
            'columns' => [
                ['name' => 'full_name', 'type' => 'TEXT', 'isNullable' => false],
            ],
        ])->assertCreated();

        $this->postJson('/api/v1/dynamic-tables/data/contacts_beta', [
            'full_name' => 'Anna',
            'hacked_field' => 'bad',
        ])
            ->assertBadRequest()
            ->assertJsonPath('status', 400);

        $this->postJson('/api/v1/dynamic-tables/data/contacts_beta', [])
            ->assertBadRequest()
            ->assertJsonPath('status', 400);
    }

    public function test_it_rejects_duplicate_schemas(): void
    {
        $payload = [
            'tableName' => 'contacts_gamma',
            'columns' => [
                ['name' => 'full_name', 'type' => 'TEXT'],
            ],
        ];

        $this->postJson('/api/v1/dynamic-tables/schemas', $payload)->assertCreated();
        $this->postJson('/api/v1/dynamic-tables/schemas', $payload)->assertStatus(409);
    }
}
