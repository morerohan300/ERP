<x-layout.app :title="__('Fiscal years')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Fiscal years')]]">
    <x-card :title="__('Fiscal years')">
        <x-slot:header>
            <x-button href="{{ route('fiscal-years.create') }}" size="sm">{{ __('New fiscal year') }}</x-button>
        </x-slot:header>
        <x-table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Name') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Start') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('End') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-end font-medium">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>
            @forelse ($years as $year)
                <tr>
                    <td class="px-4 py-3 font-medium text-navy-900 dark:text-white">{{ $year->name }}</td>
                    <td class="px-4 py-3">{{ $year->start_date?->toDateString() }}</td>
                    <td class="px-4 py-3">{{ $year->end_date?->toDateString() }}</td>
                    <td class="px-4 py-3"><x-badge :color="$year->is_closed ? 'slate' : 'success'" :dot="true">{{ $year->is_closed ? __('Closed') : __('Open') }}</x-badge></td>
                    <td class="px-4 py-3 text-end">
                        @can('update', $year)
                            <a href="{{ route('fiscal-years.edit', $year) }}" class="text-xs text-gold-600 hover:underline">{{ __('Edit') }}</a>
                        @endcan
                        @can('delete', $year)
                            <form method="POST" action="{{ route('fiscal-years.destroy', $year) }}" class="mt-1" onsubmit="return confirm('{{ __('Delete this fiscal year?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-danger hover:underline">{{ __('Delete') }}</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state :title="__('No fiscal years')" /></td></tr>
            @endforelse
        </x-table>
    </x-card>
</x-layout.app>
