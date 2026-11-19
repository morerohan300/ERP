<x-layout.app :title="__('New company')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('New company')]]">
    <x-card :title="__('Add a company')" :subtitle="__('You will be added as the owner of the new company.')" class="max-w-4xl">
        <form method="POST" action="{{ route('companies.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @include('core::companies._form')
            <div class="flex gap-2">
                <x-button type="submit">{{ __('Create company') }}</x-button>
                <x-button href="{{ route('dashboard') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
