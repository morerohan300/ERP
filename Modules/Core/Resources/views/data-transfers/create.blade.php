<x-layout.app :title="__('New data transfer')" :breadcrumbs="[['label' => __('System')], ['label' => __('Data transfers'), 'url' => route('data-transfers.index')], ['label' => __('New')]]">
    <x-card :title="__('New data transfer')" class="max-w-2xl">
        <form method="POST" action="{{ route('data-transfers.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <x-form.select name="direction" :label="__('Direction')" :options="['export' => __('Export'), 'import' => __('Import')]" :value="old('direction', 'export')" required />
            <x-form.select name="module" :label="__('Module')" :options="['core' => __('Core')]" :value="old('module', 'core')" required />
            <x-form.select name="entity" :label="__('Entity')" :options="array_combine($entities, array_map(fn ($e) => str_replace('_', ' ', ucfirst($e)), $entities))" :value="old('entity')" required />
            <x-form.select name="format" :label="__('Format')" :options="['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (.csv)']" :value="old('format', 'xlsx')" required />
            <x-form.input name="file" :label="__('File (imports only)')" type="file" accept=".xlsx,.csv" />
            <x-button type="submit">{{ __('Start transfer') }}</x-button>
        </form>
    </x-card>
</x-layout.app>
