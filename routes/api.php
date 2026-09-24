<?php

use App\Http\Controllers\Api\V1\DynamicTableDataController;
use App\Http\Controllers\Api\V1\DynamicTableSchemaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/dynamic-tables')->group(function (): void {
    Route::get('schemas', [DynamicTableSchemaController::class, 'index']);
    Route::post('schemas', [DynamicTableSchemaController::class, 'store']);
    Route::get('schemas/{tableName}', [DynamicTableSchemaController::class, 'show']);

    Route::get('data/{tableName}', [DynamicTableDataController::class, 'index']);
    Route::post('data/{tableName}', [DynamicTableDataController::class, 'store']);
    Route::get('data/{tableName}/{id}', [DynamicTableDataController::class, 'show']);
    Route::put('data/{tableName}/{id}', [DynamicTableDataController::class, 'update']);
    Route::delete('data/{tableName}/{id}', [DynamicTableDataController::class, 'destroy']);
});
