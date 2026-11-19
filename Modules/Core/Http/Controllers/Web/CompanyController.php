<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Modules\Core\Http\Requests\StoreCompanyRequest;
use Modules\Core\Http\Requests\UpdateCompanyRequest;
use Modules\Core\Models\Company;
use Modules\Core\Models\Currency;
use Modules\Core\Services\CompanyService;

final class CompanyController extends Controller
{
    public function onboarding(): View|RedirectResponse
    {
        if (auth()->user()->companies()->exists()) {
            return redirect()->route('dashboard');
        }

        return view('core::companies.onboarding', [
            'currencies' => Currency::query()->orderBy('code')->pluck('name', 'code'),
        ]);
    }

    public function store(StoreCompanyRequest $request, CompanyService $companies): RedirectResponse
    {
        $data = $request->validated();
        $logo = $data['logo'] ?? null;
        unset($data['logo']);

        /** @var User $user */
        $user = $request->user();
        $company = $companies->create(array_filter($data, fn ($value) => $value !== null), $user);

        if ($logo !== null) {
            $companies->update($company, [], $logo);
        }

        return $this->status('dashboard', __('Company ":name" created. Welcome to Ledgerline!', ['name' => $company->name]));
    }

    public function switch(Request $request, Company $company): RedirectResponse
    {
        Gate::authorize('switchTo', $company);

        $request->session()->put(config('tenancy.session_key'), $company->getKey());

        return $this->status('dashboard', __('Now working in :company.', ['company' => $company->name]));
    }

    public function edit(): View
    {
        Gate::authorize('view', app(CurrentCompany::class)->getOrFail());

        return view('core::settings.company', [
            'company' => app(CurrentCompany::class)->getOrFail(),
            'currencies' => Currency::query()->orderBy('code')->pluck('name', 'code'),
            'locales' => config('locales.supported'),
            'dateFormats' => config('locales.date_formats'),
        ]);
    }

    public function update(UpdateCompanyRequest $request, CompanyService $companies): RedirectResponse
    {
        $company = app(CurrentCompany::class)->getOrFail();
        $data = $request->validated();
        $logo = $data['logo'] ?? null;
        unset($data['logo']);

        $companies->update($company, $data, $logo);

        return $this->status('settings.company', __('Company profile updated.'));
    }

    public function create(): View
    {
        Gate::authorize('create', Company::class);

        return view('core::companies.create', [
            'currencies' => Currency::query()->orderBy('code')->pluck('name', 'code'),
        ]);
    }
}
