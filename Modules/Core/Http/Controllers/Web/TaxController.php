<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Http\Requests\StoreTaxRequest;
use Modules\Core\Http\Requests\UpdateTaxRequest;
use Modules\Core\Models\Tax;
use Modules\Core\Repositories\TaxRepository;

final class TaxController extends Controller
{
    public function __construct(private readonly TaxRepository $taxes)
    {
        $this->authorizeResource(Tax::class, 'tax');
    }

    public function index(): View
    {
        return view('core::taxes.index');
    }

    public function create(): View
    {
        return view('core::taxes.create');
    }

    public function store(StoreTaxRequest $request): RedirectResponse
    {
        $this->taxes->create($request->validated());

        return $this->status('taxes.index', __('Tax created.'));
    }

    public function show(Tax $tax): RedirectResponse
    {
        return redirect()->route('taxes.edit', $tax);
    }

    public function edit(Tax $tax): View
    {
        return view('core::taxes.edit', ['tax' => $tax]);
    }

    public function update(UpdateTaxRequest $request, Tax $tax): RedirectResponse
    {
        $this->taxes->update($tax, $request->validated());

        return $this->status('taxes.index', __('Tax updated.'));
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        $this->taxes->delete($tax);

        return $this->status('taxes.index', __('Tax deleted.'));
    }
}
