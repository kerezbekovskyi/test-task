<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_dynamic_table_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name')->unique();
            $table->string('user_friendly_name')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('app_dynamic_column_definitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('table_definition_id')
                ->constrained('app_dynamic_table_definitions')
                ->cascadeOnDelete();
            $table->string('column_name');
            $table->string('column_type', 50);
            $table->string('postgres_column_type', 100);
            $table->boolean('is_nullable')->default(true);
            $table->boolean('is_primary_key_internal')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['table_definition_id', 'column_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_dynamic_column_definitions');
        Schema::dropIfExists('app_dynamic_table_definitions');
    }
};
