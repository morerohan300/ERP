<x-layout.app :title="__('Edit tax')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Taxes'), 'url' => route('taxes.index')], ['label' => $tax->name]]">
    <x-card :title="__('Edit :name', ['name' => $tax->name])" class="max-w-3xl">
        <form method="POST" action="{{ route('taxes.update', $tax) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('core::taxes._form')
            <div class="flex flex-wrap gap-2">
                <x-button type="submit">{{ __('Save changes') }}</x-button>
                <x-button href="{{ route('taxes.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
                @can('delete', $tax)
                    <form method="POST" action="{{ route('taxes.destroy', $tax) }}" class="ms-auto" onsubmit="return confirm('{{ __('Delete this tax?') }}')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">{{ __('Delete') }}</x-button>
                    </form>
                @endcan
            </div>
        </form>
    </x-card>
</x-layout.app>
