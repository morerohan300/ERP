<x-layout.app :title="__('Add currency')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Currencies'), 'url' => route('currencies.index')], ['label' => __('Add')]]">
    <x-card :title="__('New currency')" class="max-w-xl">
        <form method="POST" action="{{ route('currencies.store') }}" class="space-y-4">
            @csrf
            <x-form.input name="code" :label="__('Code')" :value="old('code')" placeholder="USD" required maxlength="3" />
            <x-form.input name="name" :label="__('Name')" :value="old('name')" required />
            <x-form.input name="symbol" :label="__('Symbol')" :value="old('symbol')" required />
            <x-form.input name="decimals" :label="__('Decimals')" type="number" :value="old('decimals', 2)" required />
            <div class="flex gap-2">
                <x-button type="submit">{{ __('Create currency') }}</x-button>
                <x-button href="{{ route('currencies.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
