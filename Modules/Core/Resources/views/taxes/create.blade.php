<x-layout.app :title="__('Add tax')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Taxes'), 'url' => route('taxes.index')], ['label' => __('Add')]]">
    <x-card :title="__('New tax')" class="max-w-3xl">
        <form method="POST" action="{{ route('taxes.store') }}" class="space-y-5">
            @csrf
            @include('core::taxes._form', ['tax' => null])
            <div class="flex gap-2">
                <x-button type="submit">{{ __('Create tax') }}</x-button>
                <x-button href="{{ route('taxes.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
