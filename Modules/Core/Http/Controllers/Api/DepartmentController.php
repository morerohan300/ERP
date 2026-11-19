<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Core\Http\Requests\StoreDepartmentRequest;
use Modules\Core\Http\Requests\UpdateDepartmentRequest;
use Modules\Core\Http\Resources\DepartmentResource;
use Modules\Core\Models\Department;
use Modules\Core\Repositories\DepartmentRepository;

final class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentRepository $departments) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        [$filters, $perPage] = $this->listParameters($request);

        return DepartmentResource::collection($this->departments->paginate($filters, $perPage));
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        return (new DepartmentResource($this->departments->create($request->validated())))->response()->setStatusCode(201);
    }

    public function show(Department $department): DepartmentResource
    {
        return new DepartmentResource($department);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): DepartmentResource
    {
        return new DepartmentResource($this->departments->update($department, $request->validated()));
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->departments->delete($department);

        return response()->json(null, 204);
    }
}
