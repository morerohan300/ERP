<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Core\Http\Requests\StoreTaxRequest;
use Modules\Core\Http\Requests\UpdateTaxRequest;
use Modules\Core\Http\Resources\TaxResource;
use Modules\Core\Models\Tax;
use Modules\Core\Repositories\TaxRepository;

final class TaxController extends Controller
{
    public function __construct(private readonly TaxRepository $taxes) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        [$filters, $perPage] = $this->listParameters($request);

        return TaxResource::collection($this->taxes->paginate($filters, $perPage));
    }

    public function store(StoreTaxRequest $request): JsonResponse
    {
        return (new TaxResource($this->taxes->create($request->validated())))->response()->setStatusCode(201);
    }

    public function show(Tax $tax): TaxResource
    {
        return new TaxResource($tax);
    }

    public function update(UpdateTaxRequest $request, Tax $tax): TaxResource
    {
        return new TaxResource($this->taxes->update($tax, $request->validated()));
    }

    public function destroy(Tax $tax): JsonResponse
    {
        $this->taxes->delete($tax);

        return response()->json(null, 204);
    }
}
