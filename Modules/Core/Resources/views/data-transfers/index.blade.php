<x-layout.app :title="__('Data transfers')" :breadcrumbs="[['label' => __('System')], ['label' => __('Data transfers')]]">
    <x-card :title="__('Imports & exports')">
        <x-slot:header>
            <x-button href="{{ route('data-transfers.create') }}" size="sm">{{ __('New transfer') }}</x-button>
        </x-slot:header>

        <x-table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Direction') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Entity') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Progress') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Requested by') }}</th>
                    <th class="px-4 py-3 text-end font-medium">{{ __('Actions') }}</th>
                </tr>
            </x-slot:head>

            @forelse ($transfers as $transfer)
                <tr>
                    <td class="px-4 py-3 capitalize">{{ $transfer->direction }}</td>
                    <td class="px-4 py-3 text-sm">{{ str_replace('_', ' ', ucfirst($transfer->entity)) }}</td>
                    <td class="px-4 py-3">
                        <x-badge :color="match ($transfer->status) {
                            'completed' => 'success',
                            'failed' => 'danger',
                            'processing' => 'warning',
                            default => 'slate',
                        }" :dot="true">{{ ucfirst($transfer->status) }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-xs tabular-nums">
                        {{ $transfer->progressPercent() }}%
                        @if ($transfer->total_rows)<span class="text-slate-400">({{ $transfer->processed_rows }}/{{ $transfer->total_rows }})</span>@endif
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $transfer->creator?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-end">
                        @if ($transfer->file_path)
                            <a href="{{ route('data-transfers.download', $transfer) }}" class="text-xs text-gold-600 hover:underline">{{ __('Download') }}</a>
                        @endif
                        @if ($transfer->errors)
                            <p class="mt-1 text-xs text-danger">{{ implode('; ', array_slice($transfer->errors, 0, 2)) }}</p>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty-state :title="__('No transfers yet')" /></td></tr>
            @endforelse

            <x-slot:footer>{{ $transfers->links() }}</x-slot:footer>
        </x-table>
    </x-card>
</x-layout.app>
