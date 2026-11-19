<div>
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-64">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Name, code, email or phone') }}"
                   class="w-full rounded-control border-slate-300 bg-white text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white">
        </div>
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Status') }}</label>
            <select wire:model.live="status" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                <option value="">{{ __('All') }}</option>
                <option value="active">{{ __('Active') }}</option>
                <option value="inactive">{{ __('Inactive') }}</option>
            </select>
        </div>
        <x-button href="{{ route('branches.create') }}" size="sm" class="ms-auto">{{ __('Add branch') }}</x-button>
    </div>

    <x-table>
        <x-slot:head>
            <tr>
                <th class="px-4 py-3 text-start font-medium"><button wire:click="sortBy('name')">{{ __('Branch') }} @if ($sort === 'name') {{ $direction === 'asc' ? '↑' : '↓' }} @endif</button></th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Code') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Contact') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Departments') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Status') }}</th>
                <th class="px-4 py-3 text-end font-medium">{{ __('Actions') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($branches as $branch)
            <tr class="hover:bg-slate-50 dark:hover:bg-navy-800/40">
                <td class="px-4 py-3">
                    <p class="font-medium text-navy-900 dark:text-white">{{ $branch->name }}</p>
                    @if ($branch->is_head_office)<x-badge color="gold" class="mt-1">{{ __('Head office') }}</x-badge>@endif
                </td>
                <td class="px-4 py-3 font-mono text-xs">{{ $branch->code }}</td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ $branch->email ?: '—' }}<br>{{ $branch->phone ?: '' }}</td>
                <td class="px-4 py-3 text-sm">{{ $branch->departments_count }}</td>
                <td class="px-4 py-3"><x-badge :color="$branch->is_active ? 'success' : 'slate'" :dot="true">{{ $branch->is_active ? __('Active') : __('Inactive') }}</x-badge></td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('branches.edit', $branch) }}" class="text-xs text-gold-600 hover:underline">{{ __('Edit') }}</a>
                        @can('delete', $branch)
                            <button type="button" wire:click="remove('{{ $branch->uuid }}')" wire:confirm="{{ __('Delete this branch?') }}" class="text-xs text-danger hover:underline">{{ __('Delete') }}</button>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-empty-state :title="__('No branches found')" :description="__('Add your first branch to get started.')" /></td></tr>
        @endforelse

        <x-slot:footer>{{ $branches->links() }}</x-slot:footer>
    </x-table>
</div>
