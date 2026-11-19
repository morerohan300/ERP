<x-layout.app :title="__('Edit custom field')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Custom fields'), 'url' => route('custom-fields.index')], ['label' => $field->label]]">
    <x-card :title="__('Edit :label', ['label' => $field->label])" class="max-w-3xl">
        <form method="POST" action="{{ route('custom-fields.update', $field) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('core::custom-fields._form')
            <div class="flex flex-wrap gap-2">
                <x-button type="submit">{{ __('Save changes') }}</x-button>
                <x-button href="{{ route('custom-fields.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
                @can('delete', $field)
                    <form method="POST" action="{{ route('custom-fields.destroy', $field) }}" class="ms-auto" onsubmit="return confirm('{{ __('Delete this field?') }}')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">{{ __('Delete') }}</x-button>
                    </form>
                @endcan
            </div>
        </form>
    </x-card>
</x-layout.app>
