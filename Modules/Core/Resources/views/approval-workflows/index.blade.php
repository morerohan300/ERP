<x-layout.app :title="__('Approval workflows')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Approval workflows')]]">
    <x-card :title="__('Approval workflows')">
        <x-slot:header>
            <x-button href="{{ route('approval-workflows.create') }}" size="sm">{{ __('New workflow') }}</x-button>
        </x-slot:header>
        <div class="space-y-3">
            @forelse ($workflows as $workflow)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-control border border-slate-200 p-4 dark:border-navy-800">
                    <div>
                        <p class="font-heading text-sm text-navy-900 dark:text-white">
                            {{ $workflow->name }}
                            <x-badge class="ms-1">{{ str_replace('_', ' ', $workflow->document_type) }}</x-badge>
                        </p>
                        <p class="mt-1 text-xs text-slate-500">{{ $workflow->steps->count() }} {{ __('steps') }} · {{ __('Priority') }} {{ $workflow->priority }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-badge :color="$workflow->is_active ? 'success' : 'slate'" :dot="true">{{ $workflow->is_active ? __('Active') : __('Inactive') }}</x-badge>
                        @can('update', $workflow)
                            <a href="{{ route('approval-workflows.edit', $workflow) }}" class="text-xs text-gold-600 hover:underline">{{ __('Edit') }}</a>
                        @endcan
                        @can('delete', $workflow)
                            <form method="POST" action="{{ route('approval-workflows.destroy', $workflow) }}" onsubmit="return confirm('{{ __('Delete this workflow?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-danger hover:underline">{{ __('Delete') }}</button>
                            </form>
                        @endcan
                    </div>
                </div>
            @empty
                <x-empty-state :title="__('No workflows configured')" :description="__('Create a workflow to route documents for approval.')" />
            @endforelse
        </div>
    </x-card>
</x-layout.app>
