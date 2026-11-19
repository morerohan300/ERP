<x-layout.app :title="__('New approval workflow')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Approval workflows'), 'url' => route('approval-workflows.index')], ['label' => __('New')]]">
    <x-card :title="__('New approval workflow')">
        <form method="POST" action="{{ route('approval-workflows.store') }}" class="space-y-5" x-data="{ steps: [] }">
            @csrf
            @include('core::approval-workflows._form', ['workflow' => null])
            <div class="flex gap-2 border-t border-slate-100 pt-4 dark:border-navy-800">
                <x-button type="submit">{{ __('Create workflow') }}</x-button>
                <x-button href="{{ route('approval-workflows.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
