<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Modules\Core\Models\Currency;
use Modules\Core\Models\ExchangeRate;

final class CurrencyController extends Controller
{
    public function index(): View
    {
        Gate::authorize('core.currency.view');

        return view('core::currencies.index', [
            'currencies' => Currency::query()->orderBy('code')->get(),
            'rates' => ExchangeRate::query()->with(['fromCurrency', 'toCurrency'])->latest('effective_date')->get(),
            'enabled' => app(CurrentCompany::class)->get()?->currencies()->pluck('currency_code')->all() ?? [],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('core.currency.create');

        return view('core::currencies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('core.currency.create');
        $data = $request->validate([
            'code' => ['required', 'string', 'size:3', 'unique:currencies,code'],
            'name' => ['required', 'string', 'max:80'],
            'symbol' => ['required', 'string', 'max:10'],
            'decimals' => ['required', 'integer', 'min:0', 'max:6'],
        ]);
        $data['code'] = mb_strtoupper($data['code']);

        Currency::query()->create($data);

        return $this->status('currencies.index', __('Currency created.'));
    }

    public function edit(Currency $currency): View
    {
        Gate::authorize('core.currency.edit');

        return view('core::currencies.edit', ['currency' => $currency]);
    }

    public function update(Request $request, Currency $currency): RedirectResponse
    {
        Gate::authorize('core.currency.edit');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'symbol' => ['required', 'string', 'max:10'],
            'decimals' => ['required', 'integer', 'min:0', 'max:6'],
        ]);

        $currency->update($data);

        return $this->status('currencies.index', __('Currency updated.'));
    }

    public function destroy(Currency $currency): RedirectResponse
    {
        Gate::authorize('core.currency.delete');

        if (app(CurrentCompany::class)->get()?->base_currency_code === $currency->code) {
            return back()->with('error', __('The company base currency cannot be deleted.'));
        }

        $currency->delete();

        return $this->status('currencies.index', __('Currency deleted.'));
    }

    public function toggle(Currency $currency): RedirectResponse
    {
        Gate::authorize('core.currency.edit');
        $company = app(CurrentCompany::class)->getOrFail();

        if ($company->currencies()->whereKey($currency->code)->exists()) {
            $company->currencies()->detach($currency->code);
            $message = __('Currency disabled for this company.');
        } else {
            $company->currencies()->attach($currency->code, ['is_active' => true]);
            $message = __('Currency enabled for this company.');
        }

        return $this->status('currencies.index', $message);
    }

    public function storeRate(Request $request): RedirectResponse
    {
        Gate::authorize('core.currency.edit');
        $data = $request->validate([
            'from_currency' => ['required', 'exists:currencies,code'],
            'to_currency' => ['required', 'exists:currencies,code', 'different:from_currency'],
            'rate' => ['required', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
        ]);

        ExchangeRate::query()->updateOrCreate(
            array_intersect_key($data, array_flip(['from_currency', 'to_currency', 'effective_date'])),
            ['rate' => $data['rate']],
        );

        return $this->status('currencies.index', __('Exchange rate saved.'));
    }

    public function destroyRate(ExchangeRate $exchangeRate): RedirectResponse
    {
        Gate::authorize('core.currency.delete');
        $exchangeRate->delete();

        return $this->status('currencies.index', __('Exchange rate deleted.'));
    }
}
