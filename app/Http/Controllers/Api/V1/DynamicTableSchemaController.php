<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDynamicTableSchemaRequest;
use App\Http\Resources\DynamicTableSchemaResource;
use App\Http\Resources\DynamicTableSummaryResource;
use App\Services\DynamicTables\DynamicTableSchemaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DynamicTableSchemaController extends Controller
{
    public function __construct(private readonly DynamicTableSchemaService $schemas) {}

    public function index(): AnonymousResourceCollection
    {
        return DynamicTableSummaryResource::collection($this->schemas->list());
    }

    public function store(StoreDynamicTableSchemaRequest $request): JsonResponse
    {
        return (new DynamicTableSchemaResource($this->schemas->create($request->validated())))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $tableName): DynamicTableSchemaResource
    {
        return new DynamicTableSchemaResource($this->schemas->get($tableName));
    }
}
