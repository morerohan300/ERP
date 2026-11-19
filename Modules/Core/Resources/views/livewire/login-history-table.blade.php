<div>
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-64">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Name, email or IP') }}"
                   class="w-full rounded-control border-slate-300 bg-white text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white">
        </div>
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Result') }}</label>
            <select wire:model.live="status" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                <option value="">{{ __('All') }}</option>
                <option value="successful">{{ __('Successful') }}</option>
                <option value="failed">{{ __('Failed') }}</option>
            </select>
        </div>
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Event') }}</label>
            <select wire:model.live="event" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                <option value="">{{ __('All') }}</option>
                <option value="login">{{ __('Login') }}</option>
                <option value="logout">{{ __('Logout') }}</option>
                <option value="failed">{{ __('Failed login') }}</option>
            </select>
        </div>
    </div>

    <x-table>
        <x-slot:head>
            <tr>
                <th class="px-4 py-3 text-start font-medium">{{ __('User') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Event') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Location') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Device') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('When') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($logins as $login)
            <tr class="hover:bg-slate-50 dark:hover:bg-navy-800/40">
                <td class="px-4 py-3">
                    <p class="text-sm font-medium text-navy-900 dark:text-white">{{ $login->user?->name ?? $login->email ?? '—' }}</p>
                    <p class="text-xs text-slate-500">{{ $login->user?->email ?? $login->email }}</p>
                </td>
                <td class="px-4 py-3"><x-badge :color="$login->successful ? 'success' : 'danger'" :dot="true">{{ ucfirst($login->event) }}</x-badge></td>
                <td class="px-4 py-3 font-mono text-xs">{{ $login->ip_address ?: '—' }}</td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ $login->browser() }} · {{ $login->platform() }}</td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ $login->occurred_at?->diffForHumans() }}</td>
            </tr>
        @empty
            <tr><td colspan="5"><x-empty-state :title="__('No login history')" /></td></tr>
        @endforelse

        <x-slot:footer>{{ $logins->links() }}</x-slot:footer>
    </x-table>
</div>
