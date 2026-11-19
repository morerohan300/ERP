<x-layout.app :title="__('Document numbering')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Document numbering')]]">
    <x-card :title="__('Number sequences')" :subtitle="__('Tokens: {PREFIX}, {YYYY}, {YY}, {MM}, {DD}, {SEQ}, {SEQ:4}.')">
        <div class="space-y-4">
            @forelse ($sequences as $sequence)
                <form method="POST" action="{{ route('numbering.update', $sequence) }}" class="grid gap-3 rounded-control border border-slate-200 p-4 md:grid-cols-5 dark:border-navy-800">
                    @csrf
                    @method('PUT')
                    <div class="md:col-span-5 flex items-center justify-between">
                        <p class="font-heading text-sm text-navy-900 dark:text-white">{{ str_replace(['_', '-'], ' ', $sequence->document_type) }}</p>
                        <x-badge color="gold">{{ __('Next: :number', ['number' => $sequence->next_number]) }}</x-badge>
                    </div>
                    <x-form.input name="prefix" :label="__('Prefix')" :value="$sequence->prefix" required />
                    <x-form.input name="format" :label="__('Format')" :value="$sequence->format" required />
                    <x-form.input name="padding" :label="__('Padding')" type="number" :value="$sequence->padding" required />
                    <x-form.select name="reset_period" :label="__('Reset')" :options="['never' => __('Never'), 'yearly' => __('Yearly'), 'monthly' => __('Monthly')]" :value="$sequence->reset_period" required />
                    <div class="flex items-end"><x-button type="submit" size="sm">{{ __('Save') }}</x-button></div>
                </form>
            @empty
                <x-empty-state :title="__('No sequences configured')" :description="__('Sequences are created automatically when documents are first issued.')" />
            @endforelse
        </div>
    </x-card>
</x-layout.app>
