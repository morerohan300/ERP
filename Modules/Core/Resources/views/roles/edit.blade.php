<x-layout.app :title="__('Edit role')" :breadcrumbs="[['label' => __('Users & Access')], ['label' => __('Roles & permissions'), 'url' => route('roles.index')], ['label' => $role->name]]">
    <x-card :title="__('Edit :name', ['name' => $role->name])">
        <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('core::roles._form')
            <div class="flex gap-2 border-t border-slate-100 pt-4 dark:border-navy-800">
                <x-button type="submit">{{ __('Save changes') }}</x-button>
                <x-button href="{{ route('roles.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
