<x-layout.app :title="__('Edit department')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Departments'), 'url' => route('departments.index')], ['label' => $department->name]]">
    <x-card :title="__('Edit :name', ['name' => $department->name])" class="max-w-3xl">
        <form method="POST" action="{{ route('departments.update', $department) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('core::departments._form')
            <div class="flex flex-wrap gap-2">
                <x-button type="submit">{{ __('Save changes') }}</x-button>
                <x-button href="{{ route('departments.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
                @can('delete', $department)
                    <form method="POST" action="{{ route('departments.destroy', $department) }}" class="ms-auto" onsubmit="return confirm('{{ __('Delete this department?') }}')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">{{ __('Delete') }}</x-button>
                    </form>
                @endcan
            </div>
        </form>
    </x-card>
</x-layout.app>
