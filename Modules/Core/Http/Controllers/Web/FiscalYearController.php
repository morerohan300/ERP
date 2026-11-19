<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Http\Requests\StoreFiscalYearRequest;
use Modules\Core\Http\Requests\UpdateFiscalYearRequest;
use Modules\Core\Models\FiscalYear;

final class FiscalYearController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FiscalYear::class, 'fiscal_year');
    }

    public function index(): View
    {
        return view('core::fiscal-years.index', [
            'years' => FiscalYear::query()->orderByDesc('start_date')->get(),
        ]);
    }

    public function create(): View
    {
        return view('core::fiscal-years.create');
    }

    public function store(StoreFiscalYearRequest $request): RedirectResponse
    {
        FiscalYear::query()->create($request->validated());

        return $this->status('fiscal-years.index', __('Fiscal year created.'));
    }

    public function show(FiscalYear $fiscalYear): RedirectResponse
    {
        return redirect()->route('fiscal-years.edit', $fiscalYear);
    }

    public function edit(FiscalYear $fiscalYear): View
    {
        return view('core::fiscal-years.edit', ['fiscalYear' => $fiscalYear]);
    }

    public function update(UpdateFiscalYearRequest $request, FiscalYear $fiscalYear): RedirectResponse
    {
        $data = $request->validated();
        if (($data['is_closed'] ?? false) && $fiscalYear->closed_at === null) {
            $data['closed_at'] = now();
        } elseif (! ($data['is_closed'] ?? false)) {
            $data['closed_at'] = null;
        }
        $fiscalYear->update($data);

        return $this->status('fiscal-years.index', __('Fiscal year updated.'));
    }

    public function destroy(FiscalYear $fiscalYear): RedirectResponse
    {
        $fiscalYear->delete();

        return $this->status('fiscal-years.index', __('Fiscal year deleted.'));
    }
}
