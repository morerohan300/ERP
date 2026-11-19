<x-layout.app :title="__('Approvals')" :breadcrumbs="[['label' => __('Approvals')]]">
    <div class="grid gap-6 lg:grid-cols-2">
        <x-card :title="__('Awaiting your decision')">
            <div class="space-y-2">
                @forelse ($pending as $request)
                    <a href="{{ route('approvals.show', $request) }}" class="flex items-center justify-between gap-3 rounded-control border border-slate-200 p-3 hover:border-gold-500 dark:border-navy-800">
                        <div>
                            <p class="text-sm font-medium text-navy-900 dark:text-white">{{ $request->number }}</p>
                            <p class="text-xs text-slate-500">{{ $request->workflow?->name }} · {{ $request->requester?->name }}</p>
                        </div>
                        <x-badge color="warning" :dot="true">{{ __('Pending') }}</x-badge>
                    </a>
                @empty
                    <x-empty-state :title="__('Nothing to review')" />
                @endforelse
            </div>
        </x-card>

        <x-card :title="__('My requests')">
            <div class="space-y-2">
                @forelse ($mine as $request)
                    <a href="{{ route('approvals.show', $request) }}" class="flex items-center justify-between gap-3 rounded-control border border-slate-200 p-3 hover:border-gold-500 dark:border-navy-800">
                        <div>
                            <p class="text-sm font-medium text-navy-900 dark:text-white">{{ $request->number }}</p>
                            <p class="text-xs text-slate-500">{{ $request->workflow?->name }} · {{ $request->created_at?->diffForHumans() }}</p>
                        </div>
                        <x-badge :color="match ($request->status) { 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'slate', default => 'warning' }" :dot="true">{{ ucfirst($request->status) }}</x-badge>
                    </a>
                @empty
                    <x-empty-state :title="__('No requests yet')" />
                @endforelse
            </div>
        </x-card>
    </div>
</x-layout.app>
