<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateDynamicTableRecordsRequest;
use App\Http\Requests\StoreDynamicTableRecordRequest;
use App\Http\Requests\UpdateDynamicTableRecordRequest;
use App\Services\DynamicTables\DynamicTableDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class DynamicTableDataController extends Controller
{
    public function __construct(private readonly DynamicTableDataService $data) {}

    public function store(StoreDynamicTableRecordRequest $request, string $tableName): JsonResponse
    {
        return response()->json($this->data->create($tableName, $request->all()), 201);
    }

    public function index(PaginateDynamicTableRecordsRequest $request, string $tableName): JsonResponse
    {
        return response()->json($this->data->paginate(
            $tableName,
            (int) $request->integer('page', 0),
            (int) $request->integer('size', 20),
        ));
    }

    public function show(string $tableName, int|string $id): JsonResponse
    {
        return response()->json($this->data->find($tableName, $id));
    }

    public function update(UpdateDynamicTableRecordRequest $request, string $tableName, int|string $id): JsonResponse
    {
        return response()->json($this->data->update($tableName, $id, $request->all()));
    }

    public function destroy(string $tableName, int|string $id): Response
    {
        $this->data->delete($tableName, $id);

        return response()->noContent();
    }
}
