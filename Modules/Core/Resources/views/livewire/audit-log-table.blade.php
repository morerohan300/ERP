<div>
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-64">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Description or event') }}"
                   class="w-full rounded-control border-slate-300 bg-white text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white">
        </div>
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Event') }}</label>
            <input type="text" wire:model.live.debounce.300ms="event" placeholder="created, updated…"
                   class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
        </div>
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('From') }}</label>
            <input type="date" wire:model.live="from" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
        </div>
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('To') }}</label>
            <input type="date" wire:model.live="to" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
        </div>
        @can('core.audit_log.export')
            <x-button href="{{ route('audit-log.export') }}" variant="secondary" size="sm" class="ms-auto">{{ __('Export CSV') }}</x-button>
        @endcan
    </div>

    <x-table>
        <x-slot:head>
            <tr>
                <th class="px-4 py-3 text-start font-medium">{{ __('Date') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Event') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Description') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Subject') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Causer') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($logs as $log)
            <tr class="hover:bg-slate-50 dark:hover:bg-navy-800/40">
                <td class="px-4 py-3 text-xs text-slate-500">{{ $log->created_at }}</td>
                <td class="px-4 py-3"><x-badge :color="$log->event === 'deleted' ? 'danger' : ($log->event === 'created' ? 'success' : 'slate')">{{ $log->event ?: '—' }}</x-badge></td>
                <td class="px-4 py-3 text-sm">{{ $log->description }}</td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '—' }}</td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ $log->causer_type ? class_basename($log->causer_type).' #'.$log->causer_id : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5"><x-empty-state :title="__('No activity recorded')" /></td></tr>
        @endforelse

        <x-slot:footer>{{ $logs->links() }}</x-slot:footer>
    </x-table>
</div>
