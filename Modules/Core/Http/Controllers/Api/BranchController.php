<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Core\Http\Requests\StoreBranchRequest;
use Modules\Core\Http\Requests\UpdateBranchRequest;
use Modules\Core\Http\Resources\BranchResource;
use Modules\Core\Models\Branch;
use Modules\Core\Repositories\BranchRepository;

final class BranchController extends Controller
{
    public function __construct(private readonly BranchRepository $branches) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        [$filters, $perPage] = $this->listParameters($request);

        return BranchResource::collection($this->branches->paginate($filters, $perPage));
    }

    public function store(StoreBranchRequest $request): JsonResponse
    {
        return (new BranchResource($this->branches->create($request->validated())))->response()->setStatusCode(201);
    }

    public function show(Branch $branch): BranchResource
    {
        return new BranchResource($branch);
    }

    public function update(UpdateBranchRequest $request, Branch $branch): BranchResource
    {
        return new BranchResource($this->branches->update($branch, $request->validated()));
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $this->branches->delete($branch);

        return response()->json(null, 204);
    }
}
