<x-layout.app :title="__('Add branch')" :breadcrumbs="[['label' => __('Organization'), 'url' => route('branches.index')], ['label' => __('Branches'), 'url' => route('branches.index')], ['label' => __('Add')]]">
    <x-card :title="__('New branch')" class="max-w-3xl">
        <form method="POST" action="{{ route('branches.store') }}" class="space-y-5">
            @csrf
            @include('core::branches._form', ['branch' => null])
            <div class="flex gap-2">
                <x-button type="submit">{{ __('Create branch') }}</x-button>
                <x-button href="{{ route('branches.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
