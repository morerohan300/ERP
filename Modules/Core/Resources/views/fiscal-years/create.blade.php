<x-layout.app :title="__('New fiscal year')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Fiscal years'), 'url' => route('fiscal-years.index')], ['label' => __('New')]]">
    <x-card :title="__('New fiscal year')" class="max-w-xl">
        <form method="POST" action="{{ route('fiscal-years.store') }}" class="space-y-4">
            @csrf
            <x-form.input name="name" :label="__('Name')" :value="old('name')" placeholder="FY 2026" required />
            <x-form.input name="start_date" :label="__('Start date')" type="date" :value="old('start_date')" required />
            <x-form.input name="end_date" :label="__('End date')" type="date" :value="old('end_date')" required />
            <x-form.checkbox name="is_closed" :label="__('Closed')" :checked="false" />
            <div class="flex gap-2">
                <x-button type="submit">{{ __('Create fiscal year') }}</x-button>
                <x-button href="{{ route('fiscal-years.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
