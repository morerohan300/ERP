<x-layout.app :title="__('Edit approval workflow')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Approval workflows'), 'url' => route('approval-workflows.index')], ['label' => $workflow->name]]">
    <x-card :title="__('Edit :name', ['name' => $workflow->name])">
        <form method="POST" action="{{ route('approval-workflows.update', $workflow) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('core::approval-workflows._form')
            <div class="flex gap-2 border-t border-slate-100 pt-4 dark:border-navy-800">
                <x-button type="submit">{{ __('Save changes') }}</x-button>
                <x-button href="{{ route('approval-workflows.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
