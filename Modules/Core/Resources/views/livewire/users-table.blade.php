<div>
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-full sm:w-64">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Name, email or phone') }}"
                   class="w-full rounded-control border-slate-300 bg-white text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white">
        </div>
        <div class="w-40">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Role') }}</label>
            <select wire:model.live="role" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                <option value="">{{ __('All roles') }}</option>
                @foreach ($roles as $name)
                    <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
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
        <x-button href="{{ route('users.create') }}" size="sm" class="ms-auto">{{ __('Add user') }}</x-button>
    </div>

    <x-table>
        <x-slot:head>
            <tr>
                <th class="px-4 py-3 text-start font-medium"><button wire:click="sortBy('name')">{{ __('User') }} @if ($sort === 'name') {{ $direction === 'asc' ? '↑' : '↓' }} @endif</button></th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Roles') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('Status') }}</th>
                <th class="px-4 py-3 text-start font-medium">{{ __('2FA') }}</th>
                <th class="px-4 py-3 text-start font-medium"><button wire:click="sortBy('last_login_at')">{{ __('Last login') }} @if ($sort === 'last_login_at') {{ $direction === 'asc' ? '↑' : '↓' }} @endif</button></th>
                <th class="px-4 py-3 text-end font-medium">{{ __('Actions') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($users as $user)
            <tr class="hover:bg-slate-50 dark:hover:bg-navy-800/40">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        @if ($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="" class="size-9 rounded-full object-cover">
                        @else
                            <span class="grid size-9 place-items-center rounded-full bg-navy-900/10 text-xs font-semibold text-navy-900 dark:bg-white/10 dark:text-white">{{ $user->initials() }}</span>
                        @endif
                        <div>
                            <p class="font-medium text-navy-900 dark:text-white">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    @forelse ($user->roles as $role)
                        <x-badge class="me-1">{{ $role->name }}</x-badge>
                    @empty
                        <span class="text-xs text-slate-400">—</span>
                    @endforelse
                </td>
                <td class="px-4 py-3"><x-badge :color="$user->is_active ? 'success' : 'slate'" :dot="true">{{ $user->is_active ? __('Active') : __('Inactive') }}</x-badge></td>
                <td class="px-4 py-3"><x-badge :color="$user->hasTwoFactorEnabled() ? 'success' : 'slate'">{{ $user->hasTwoFactorEnabled() ? __('On') : __('Off') }}</x-badge></td>
                <td class="px-4 py-3 text-xs text-slate-500">{{ $user->last_login_at?->diffForHumans() ?? '—' }}</td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('users.edit', $user) }}" class="text-xs text-gold-600 hover:underline">{{ __('Edit') }}</a>
                        @can('delete', $user)
                            <button type="button" wire:click="remove('{{ $user->uuid }}')"
                                    wire:confirm="{{ __('Remove this user from the company?') }}"
                                    class="text-xs text-danger hover:underline">{{ __('Remove') }}</button>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-empty-state :title="__('No users found')" :description="__('Try adjusting your filters or add a user.')" /></td></tr>
        @endforelse

        <x-slot:footer>{{ $users->links() }}</x-slot:footer>
    </x-table>
</div>
