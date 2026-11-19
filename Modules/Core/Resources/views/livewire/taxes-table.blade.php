<div>
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-64">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Name or code') }}"
                   class="w-full rounded-control border-slate-300 bg-white text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white">
        </div>
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Scope') }}</label>
            <select wire:model.live="scope" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                <option value="">{{ __('All') }}</option>
                <option value="sales">{{ __('Sales') }}</option>
                <option value="purchase">{{ __('Purchase') }}</option>
                <option value="both">{{ __('Both') }}</option>
            </select>
        </div>
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Status') }}</label>
            <select wire:model.live="status" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                <option value="">{{ __('All') }}</option>
                <option value="active">{{ __('Active') }}</option>
                <option value="inactive">{{ __('Inactive') }}</option>
            </select>
        </div>
        <x-button href="{{ route('taxes.create') }}" size="sm" class="ms-auto">{{ __('Add tax') }}</x-button>
    </div>

    <x-table>
        <x-slot:head>
            <tr>
                <th class="px-4 py-3 text-start font-medium"><button wire:click="sortBy('name')">{{ __('Tax') }} @if ($sort === 'name') {{ $direction === 'asc' ? '↑' : '↓' }} @endif</button></th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Code') }}</th>
                <th class="px-4 py-3 text-start font-medium"><button wire:click="sortBy('rate')">{{ __('Rate') }} @if ($sort === 'rate') {{ $direction === 'asc' ? '↑' : '↓' }} @endif</button></th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Type') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Scope') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Status') }}</th>
                <th class="px-4 py-3 text-end font-medium">{{ __('Actions') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($taxes as $tax)
            <tr class="hover:bg-slate-50 dark:hover:bg-navy-800/40">
                <td class="px-4 py-3 font-medium text-navy-900 dark:text-white">
                    {{ $tax->name }}
                    @if ($tax->is_compound)<x-badge color="gold" class="ms-1">{{ __('Compound') }}</x-badge>@endif
                    @if ($tax->is_inclusive)<x-badge class="ms-1">{{ __('Inclusive') }}</x-badge>@endif
                </td>
                <td class="px-4 py-3 font-mono text-xs">{{ $tax->code }}</td>
                <td class="px-4 py-3 tabular-nums">{{ $tax->type === 'percent' ? $tax->rate.'%' : $tax->rate }}</td>
                <td class="px-4 py-3 capitalize">{{ $tax->type }}</td>
                <td class="px-4 py-3 capitalize">{{ $tax->scope }}</td>
                <td class="px-4 py-3"><x-badge :color="$tax->is_active ? 'success' : 'slate'" :dot="true">{{ $tax->is_active ? __('Active') : __('Inactive') }}</x-badge></td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('taxes.edit', $tax) }}" class="text-xs text-gold-600 hover:underline">{{ __('Edit') }}</a>
                        @can('delete', $tax)
                            <button type="button" wire:click="remove('{{ $tax->uuid }}')" wire:confirm="{{ __('Delete this tax?') }}" class="text-xs text-danger hover:underline">{{ __('Delete') }}</button>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7"><x-empty-state :title="__('No taxes found')" /></td></tr>
        @endforelse

        <x-slot:footer>{{ $taxes->links() }}</x-slot:footer>
    </x-table>
</div>
