<x-layout.app :title="__('New role')" :breadcrumbs="[['label' => __('Users & Access')], ['label' => __('Roles & permissions'), 'url' => route('roles.index')], ['label' => __('New')]]">
    <x-card :title="__('Create role')">
        <form method="POST" action="{{ route('roles.store') }}" class="space-y-5">
            @csrf
            @include('core::roles._form', ['role' => null])
            <div class="flex gap-2 border-t border-slate-100 pt-4 dark:border-navy-800">
                <x-button type="submit">{{ __('Create role') }}</x-button>
                <x-button href="{{ route('roles.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
