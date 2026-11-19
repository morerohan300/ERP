<x-layout.app :title="__('Dashboard')" :breadcrumbs="[['label' => __('Dashboard')]]">
    <div class="space-y-6">
        <div>
            <h2 class="font-heading text-xl text-navy-900 dark:text-white">{{ __('Good :time, :name', ['time' => now()->hour < 12 ? __('morning') : (now()->hour < 18 ? __('afternoon') : __('evening')), 'name' => auth()->user()->name]) }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Here is what is happening in your workspace today.') }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card :label="__('Users')" :value="$stats['users']" icon="☰" color="navy" :hint="__('Members of this company')" />
            <x-stat-card :label="__('Branches')" :value="$stats['branches']" icon="⌂" color="gold" />
            <x-stat-card :label="__('Departments')" :value="$stats['departments']" icon="▦" />
            <a href="{{ route('approvals.index') }}" class="block">
                <x-stat-card :label="__('Pending approvals')" :value="$stats['pending_approvals']" icon="✓" color="warning" :hint="__('Across all workflows')" />
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-card :title="__('Awaiting your approval')" :subtitle="__('Requests that need your attention')">
                @forelse ($pending as $request)
                    <a href="{{ route('approvals.show', $request) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 py-2.5 last:border-0 hover:bg-slate-50 dark:border-navy-800 dark:hover:bg-navy-800/40">
                        <div>
                            <p class="text-sm font-medium text-navy-900 dark:text-white">{{ $request->number }} · {{ $request->workflow?->name }}</p>
                            <p class="text-xs text-slate-500">{{ $request->requester?->name }} · {{ $request->created_at?->diffForHumans() }}</p>
                        </div>
                        <span class="text-xs text-gold-600">{{ __('Review') }} →</span>
                    </a>
                @empty
                    <x-empty-state :title="__('Nothing to approve')" :description="__('You are all caught up.')" />
                @endforelse
            </x-card>

            <x-card :title="__('Recent activity')">
                @forelse ($activity as $entry)
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 py-2.5 last:border-0 dark:border-navy-800">
                        <div>
                            <p class="text-sm text-navy-900 dark:text-white">{{ $entry->description }}</p>
                            <p class="text-xs text-slate-400">{{ $entry->event ?: __('event') }}</p>
                        </div>
                        <span class="whitespace-nowrap text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($entry->created_at)->diffForHumans() }}</span>
                    </div>
                @empty
                    <x-empty-state :title="__('No activity yet')" />
                @endforelse
            </x-card>
        </div>

        <x-card :title="__('Your recent sign-ins')">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($logins as $login)
                    <div class="rounded-panel border border-slate-100 p-3 text-xs dark:border-navy-800">
                        <div class="flex items-center justify-between">
                            <x-badge :color="$login->successful ? 'success' : 'danger'" :dot="true">{{ ucfirst($login->event) }}</x-badge>
                            <span class="text-slate-400">{{ $login->occurred_at?->diffForHumans() }}</span>
                        </div>
                        <p class="mt-2 font-mono text-slate-500">{{ $login->ip_address ?: '—' }}</p>
                        <p class="text-slate-400">{{ $login->browser() }} · {{ $login->platform() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">{{ __('No sign-ins recorded yet.') }}</p>
                @endforelse
            </div>
        </x-card>
    </div>
</x-layout.app>
