<x-layout.app :title="__('Edit fiscal year')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Fiscal years'), 'url' => route('fiscal-years.index')], ['label' => $fiscalYear->name]]">
    <x-card :title="__('Edit :name', ['name' => $fiscalYear->name])" class="max-w-xl">
        <form method="POST" action="{{ route('fiscal-years.update', $fiscalYear) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <x-form.input name="name" :label="__('Name')" :value="$fiscalYear->name" required />
            <x-form.input name="start_date" :label="__('Start date')" type="date" :value="$fiscalYear->start_date?->toDateString()" required />
            <x-form.input name="end_date" :label="__('End date')" type="date" :value="$fiscalYear->end_date?->toDateString()" required />
            <x-form.checkbox name="is_closed" :label="__('Closed')" :checked="$fiscalYear->is_closed" />
            <div class="flex flex-wrap gap-2">
                <x-button type="submit">{{ __('Save changes') }}</x-button>
                <x-button href="{{ route('fiscal-years.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
                @can('delete', $fiscalYear)
                    <form method="POST" action="{{ route('fiscal-years.destroy', $fiscalYear) }}" class="ms-auto" onsubmit="return confirm('{{ __('Delete this fiscal year?') }}')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">{{ __('Delete') }}</x-button>
                    </form>
                @endcan
            </div>
        </form>
    </x-card>
</x-layout.app>
