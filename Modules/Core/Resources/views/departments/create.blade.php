<x-layout.app :title="__('Add department')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Departments'), 'url' => route('departments.index')], ['label' => __('Add')]]">
    <x-card :title="__('New department')" class="max-w-3xl">
        <form method="POST" action="{{ route('departments.store') }}" class="space-y-5">
            @csrf
            @include('core::departments._form', ['department' => null])
            <div class="flex gap-2">
                <x-button type="submit">{{ __('Create department') }}</x-button>
                <x-button href="{{ route('departments.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
