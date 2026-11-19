<x-layout.app :title="__('Currencies')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Currencies')]]">
    <div class="grid gap-6 lg:grid-cols-3">
        <x-card :title="__('Currencies')" class="lg:col-span-2">
            <x-slot:header>
                <x-button href="{{ route('currencies.create') }}" size="sm">{{ __('Add currency') }}</x-button>
            </x-slot:header>
            <x-table>
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3 text-start font-medium">{{ __('Code') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('Name') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('Symbol') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('Decimals') }}</th>
                        <th class="px-4 py-3 text-start font-medium">{{ __('Enabled') }}</th>
                        <th class="px-4 py-3 text-end font-medium">{{ __('Actions') }}</th>
                    </tr>
                </x-slot:head>
                @foreach ($currencies as $currency)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs">{{ $currency->code }}</td>
                        <td class="px-4 py-3 font-medium text-navy-900 dark:text-white">{{ $currency->name }}</td>
                        <td class="px-4 py-3">{{ $currency->symbol }}</td>
                        <td class="px-4 py-3 tabular-nums">{{ $currency->decimals }}</td>
                        <td class="px-4 py-3">
                            @if (in_array($currency->code, $enabled, true))<x-badge color="success" :dot="true">{{ __('Yes') }}</x-badge>@else <span class="text-xs text-slate-400">—</span>@endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('currencies.toggle', $currency) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-gold-600 hover:underline">{{ in_array($currency->code, $enabled, true) ? __('Disable') : __('Enable') }}</button>
                                </form>
                                <a href="{{ route('currencies.edit', $currency) }}" class="text-xs text-gold-600 hover:underline">{{ __('Edit') }}</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>

        <div class="space-y-6">
            <x-card :title="__('Add exchange rate')">
                <form method="POST" action="{{ route('currencies.rates.store') }}" class="space-y-3">
                    @csrf
                    <x-form.select name="from_currency" :label="__('From')" :options="$currencies->pluck('name', 'code')" required />
                    <x-form.select name="to_currency" :label="__('To')" :options="$currencies->pluck('name', 'code')" required />
                    <x-form.input name="rate" :label="__('Rate')" type="number" step="any" required />
                    <x-form.input name="effective_date" :label="__('Effective date')" type="date" :value="now()->toDateString()" required />
                    <x-button type="submit" size="sm">{{ __('Save rate') }}</x-button>
                </form>
            </x-card>

            <x-card :title="__('Recent rates')">
                <div class="space-y-2">
                    @forelse ($rates->take(10) as $rate)
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="font-mono">{{ $rate->from_currency }} → {{ $rate->to_currency }}</span>
                            <span class="tabular-nums">{{ $rate->rate }}</span>
                            <span class="text-slate-400">{{ $rate->effective_date?->toDateString() }}</span>
                            <form method="POST" action="{{ route('currencies.rates.destroy', $rate) }}" onsubmit="return confirm('{{ __('Delete this rate?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-danger hover:underline">×</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('No exchange rates yet.') }}</p>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</x-layout.app>
