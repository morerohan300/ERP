<div>
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-64">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Name or code') }}"
                   class="w-full rounded-control border-slate-300 bg-white text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white">
        </div>
        <div class="w-48">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Branch') }}</label>
            <select wire:model.live="branchId" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($branches as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <x-button href="{{ route('departments.create') }}" size="sm" class="ms-auto">{{ __('Add department') }}</x-button>
    </div>

    <x-table>
        <x-slot:head>
            <tr>
                <th class="px-4 py-3 text-start font-medium"><button wire:click="sortBy('name')">{{ __('Department') }} @if ($sort === 'name') {{ $direction === 'asc' ? '↑' : '↓' }} @endif</button></th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Code') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Branch') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Parent') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Manager') }}</th>
                <th class="px-4 py-3 text-end font-medium">{{ __('Actions') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($departments as $department)
            <tr class="hover:bg-slate-50 dark:hover:bg-navy-800/40">
                <td class="px-4 py-3 font-medium text-navy-900 dark:text-white">{{ $department->name }}</td>
                <td class="px-4 py-3 font-mono text-xs">{{ $department->code ?: '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ $department->branch?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ $department->parent?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ $department->manager?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('departments.edit', $department) }}" class="text-xs text-gold-600 hover:underline">{{ __('Edit') }}</a>
                        @can('delete', $department)
                            <button type="button" wire:click="remove('{{ $department->uuid }}')" wire:confirm="{{ __('Delete this department?') }}" class="text-xs text-danger hover:underline">{{ __('Delete') }}</button>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-empty-state :title="__('No departments found')" /></td></tr>
        @endforelse

        <x-slot:footer>{{ $departments->links() }}</x-slot:footer>
    </x-table>
</div>
