<x-layout.app :title="__('Edit branch')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Branches'), 'url' => route('branches.index')], ['label' => $branch->name]]">
    <x-card :title="__('Edit :name', ['name' => $branch->name])" class="max-w-3xl">
        <form method="POST" action="{{ route('branches.update', $branch) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('core::branches._form')
            <div class="flex flex-wrap gap-2">
                <x-button type="submit">{{ __('Save changes') }}</x-button>
                <x-button href="{{ route('branches.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
                @can('delete', $branch)
                    <form method="POST" action="{{ route('branches.destroy', $branch) }}" class="ms-auto" onsubmit="return confirm('{{ __('Delete this branch?') }}')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger" size="md">{{ __('Delete') }}</x-button>
                    </form>
                @endcan
            </div>
        </form>
    </x-card>
</x-layout.app>
