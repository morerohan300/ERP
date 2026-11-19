<x-layout.app :title="__('New custom field')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Custom fields'), 'url' => route('custom-fields.index')], ['label' => __('New')]]">
    <x-card :title="__('New custom field')" class="max-w-3xl">
        <form method="POST" action="{{ route('custom-fields.store') }}" class="space-y-5">
            @csrf
            @include('core::custom-fields._form', ['field' => null])
            <div class="flex gap-2">
                <x-button type="submit">{{ __('Create field') }}</x-button>
                <x-button href="{{ route('custom-fields.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
