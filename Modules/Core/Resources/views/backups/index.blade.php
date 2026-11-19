<x-layout.app :title="__('Backups')" :breadcrumbs="[['label' => __('System')], ['label' => __('Backups')]]">
    <x-card :title="__('Create a backup')" :subtitle="__('A full database backup is created in the background.')" class="max-w-xl">
        <form method="POST" action="{{ route('backups.store') }}">
            @csrf
            <x-button type="submit">{{ __('Create backup') }}</x-button>
        </form>
    </x-card>

    <x-card :title="__('Available backups')" class="mt-6">
        <x-table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Created by') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Size') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Created') }}</th>
                    <th class="px-4 py-3 text-end font-medium">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse ($backups as $backup)
                <tr>
                    <td class="px-4 py-3 text-sm">{{ $backup->creator?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm tabular-nums">{{ $backup->path ? $backup->humanSize() : '—' }}</td>
                    <td class="px-4 py-3">
                        <x-badge :color="match ($backup->status) {
                            'completed' => 'success',
                            'failed' => 'danger',
                            'processing' => 'warning',
                            default => 'slate',
                        }" :dot="true">{{ ucfirst($backup->status) }}</x-badge>
                        @if ($backup->error)<p class="mt-1 text-xs text-danger">{{ $backup->error }}</p>@endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $backup->created_at?->diffForHumans() }}</td>
                    <td class="px-4 py-3 text-end">
                        <div class="flex justify-end gap-3">
                            @if ($backup->path)
                                <a href="{{ route('backups.download', $backup) }}" class="text-xs text-gold-600 hover:underline">{{ __('Download') }}</a>
                            @endif
                            @can('core.backup.delete')
                                <form method="POST" action="{{ route('backups.destroy', $backup) }}" onsubmit="return confirm('{{ __('Delete this backup?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-danger hover:underline">{{ __('Delete') }}</button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state :title="__('No backups found')" :description="__('Create your first backup using the button above.')" /></td></tr>
            @endforelse

            <x-slot:footer>{{ $backups->links() }}</x-slot:footer>
        </x-table>
    </x-card>
</x-layout.app>
