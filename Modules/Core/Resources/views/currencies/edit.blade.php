<x-layout.app :title="__('Edit currency')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Currencies'), 'url' => route('currencies.index')], ['label' => $currency->code]]">
    <x-card :title="__('Edit :code', ['code' => $currency->code])" class="max-w-xl">
        <form method="POST" action="{{ route('currencies.update', $currency) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <x-form.input name="name" :label="__('Name')" :value="$currency->name" required />
            <x-form.input name="symbol" :label="__('Symbol')" :value="$currency->symbol" required />
            <x-form.input name="decimals" :label="__('Decimals')" type="number" :value="$currency->decimals" required />
            <div class="flex flex-wrap gap-2">
                <x-button type="submit">{{ __('Save changes') }}</x-button>
                <x-button href="{{ route('currencies.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
                <form method="POST" action="{{ route('currencies.destroy', $currency) }}" class="ms-auto" onsubmit="return confirm('{{ __('Delete this currency?') }}')">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger">{{ __('Delete') }}</x-button>
                </form>
            </div>
        </form>
    </x-card>
</x-layout.app>
