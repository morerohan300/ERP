<x-layout.app :title="__('Custom fields')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Custom fields')]]">
    <x-card :title="__('Custom fields')">
        <x-slot:header>
            <x-button href="{{ route('custom-fields.create') }}" size="sm">{{ __('New field') }}</x-button>
        </x-slot:header>
        <x-table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Entity') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Label') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Name') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Type') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-end font-medium">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            @forelse ($fields as $field)
                <tr>
                    <td class="px-4 py-3 text-xs">{{ $entities[$field->entity_type] ?? $field->entity_type }}</td>
                    <td class="px-4 py-3 font-medium text-navy-900 dark:text-white">{{ $field->label }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $field->name }}</td>
                    <td class="px-4 py-3 capitalize">{{ $field->type }}</td>
                    <td class="px-4 py-3"><x-badge :color="$field->is_active ? 'success' : 'slate'" :dot="true">{{ $field->is_active ? __('Active') : __('Inactive') }}</x-badge></td>
                    <td class="px-4 py-3 text-end">
                        @can('update', $field)
                            <a href="{{ route('custom-fields.edit', $field) }}" class="text-xs text-gold-600 hover:underline">{{ __('Edit') }}</a>
                        @endcan
                        @can('delete', $field)
                            <form method="POST" action="{{ route('custom-fields.destroy', $field) }}" class="mt-1" onsubmit="return confirm('{{ __('Delete this field?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-danger hover:underline">{{ __('Delete') }}</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty-state :title="__('No custom fields')" /></td></tr>
            @endforelse
        </x-table>
    </x-card>
</x-layout.app>
