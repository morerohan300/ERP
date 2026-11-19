<x-layout.app :title="__('Roles & permissions')" :breadcrumbs="[['label' => __('Users & Access')], ['label' => __('Roles & permissions')]]">
    <x-card :title="__('Roles')" :subtitle="__('Permissions are scoped to the active company.')">
        <x-slot:header>
            <x-button href="{{ route('roles.create') }}" size="sm">{{ __('New role') }}</x-button>
        </x-slot:header>

        <x-table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Role') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Permissions') }}</th>
                    <th class="px-4 py-3 text-end font-medium">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            @forelse ($roles as $role)
                <tr>
                    <td class="px-4 py-3 font-medium text-navy-900 dark:text-white">
                        {{ $role->name }}
                        @if ($role->name === 'Administrator')<x-badge color="gold" class="ms-1">{{ __('Built-in') }}</x-badge>@endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $role->permissions->count() }}</td>
                    <td class="px-4 py-3 text-end">
                        @can('update', $role)
                            <a href="{{ route('roles.edit', $role) }}" class="text-xs text-gold-600 hover:underline">{{ __('Edit') }}</a>
                        @endcan
                        @can('delete', $role)
                            <form method="POST" action="{{ route('roles.destroy', $role) }}" class="mt-1" onsubmit="return confirm('{{ __('Delete this role?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-danger hover:underline">{{ __('Delete') }}</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="3"><x-empty-state :title="__('No roles found')" /></td></tr>
            @endforelse
        </x-table>
    </x-card>
</x-layout.app>
