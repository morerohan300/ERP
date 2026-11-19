<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Core\Http\Requests\StoreCompanyRequest;
use Modules\Core\Http\Resources\CompanyResource;
use Modules\Core\Models\Company;
use Modules\Core\Services\CompanyService;

final class CompanyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $companies = $request->user()->companies()->orderBy('name')->paginate(min(max((int) $request->input('per_page', 25), 1), 100));

        return CompanyResource::collection($companies);
    }

    public function show(Company $company): CompanyResource
    {
        return new CompanyResource($company);
    }

    public function store(StoreCompanyRequest $request, CompanyService $companies): JsonResponse
    {
        $data = $request->validated();
        unset($data['logo']);

        $company = $companies->create(array_filter($data, fn ($value) => $value !== null), $request->user());

        return (new CompanyResource($company))->response()->setStatusCode(201);
    }

    public function current(): CompanyResource
    {
        return new CompanyResource(app(CurrentCompany::class)->getOrFail());
    }
}
