<x-layout.app :title="__('Approval :number', ['number' => $request->number])" :breadcrumbs="[['label' => __('Approvals'), 'url' => route('approvals.index')], ['label' => $request->number]]">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card :title="__('Request details')">
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">{{ __('Number') }}</dt><dd class="mt-1 font-mono text-sm">{{ $request->number }}</dd></div>
                    <div><dt class="text-xs text-slate-500">{{ __('Status') }}</dt><dd class="mt-1"><x-badge :color="match ($request->status) { 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'slate', default => 'warning' }" :dot="true">{{ ucfirst($request->status) }}</x-badge></dd></div>
                    <div><dt class="text-xs text-slate-500">{{ __('Workflow') }}</dt><dd class="mt-1 text-sm">{{ $request->workflow?->name }}</dd></div>
                    <div><dt class="text-xs text-slate-500">{{ __('Requested by') }}</dt><dd class="mt-1 text-sm">{{ $request->requester?->name }}</dd></div>
                    <div><dt class="text-xs text-slate-500">{{ __('Document') }}</dt><dd class="mt-1 text-sm">{{ $request->approvable ? (method_exists($request->approvable, 'approvalLabel') ? $request->approvable->approvalLabel() : class_basename($request->approvable)) : '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">{{ __('Current step') }}</dt><dd class="mt-1 text-sm">{{ $request->currentStepModel()?->name ?? '—' }}</dd></div>
                </dl>
            </x-card>

            <x-card :title="__('Decision history')">
                <div class="space-y-3">
                    @forelse ($request->actions as $action)
                        <div class="flex gap-3 border-b border-slate-100 pb-3 last:border-0 dark:border-navy-800">
                            <x-badge :color="$action->action === 'approved' ? 'success' : ($action->action === 'rejected' ? 'danger' : 'slate')">{{ ucfirst($action->action) }}</x-badge>
                            <div>
                                <p class="text-sm text-navy-900 dark:text-white">{{ $action->user?->name }}</p>
                                @if ($action->comment)<p class="text-xs text-slate-500">{{ $action->comment }}</p>@endif
                                <p class="text-xs text-slate-400">{{ $action->acted_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('No decisions recorded yet.') }}</p>
                    @endforelse
                </div>
            </x-card>
        </div>

        <div class="space-y-6">
            @if ($canAct)
                <x-card :title="__('Take action')">
                    <div class="space-y-3">
                        <form method="POST" action="{{ route('approvals.approve', $request) }}">
                            @csrf
                            <x-form.textarea name="comment" :label="__('Comment (optional)')" rows="2" />
                            <x-button type="submit" class="mt-2 w-full">{{ __('Approve') }}</x-button>
                        </form>
                        <form method="POST" action="{{ route('approvals.reject', $request) }}">
                            @csrf
                            <x-form.textarea name="comment" :label="__('Reason for rejection')" rows="2" required />
                            <x-button type="submit" variant="danger" class="mt-2 w-full">{{ __('Reject') }}</x-button>
                        </form>
                    </div>
                </x-card>
            @endif

            @can('cancel', $request)
                @if ($request->isPending())
                    <form method="POST" action="{{ route('approvals.cancel', $request) }}" onsubmit="return confirm('{{ __('Cancel this request?') }}')">
                        @csrf
                        <x-button type="submit" variant="secondary" class="w-full">{{ __('Cancel request') }}</x-button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
</x-layout.app>
