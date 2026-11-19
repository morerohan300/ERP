<x-layout.app :title="__('Add user')" :breadcrumbs="[['label' => __('Users & Access')], ['label' => __('Users'), 'url' => route('users.index')], ['label' => __('Add')]]">
    <x-card :title="__('Add a user to :company', ['company' => app(\App\Support\Tenancy\CurrentCompany::class)->getOrFail()->name])" class="max-w-4xl">
        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @include('core::users._form', ['user' => null, 'pivot' => null])
            <div class="flex gap-2">
                <x-button type="submit">{{ __('Add user') }}</x-button>
                <x-button href="{{ route('users.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
            </div>
        </form>
    </x-card>
</x-layout.app>
