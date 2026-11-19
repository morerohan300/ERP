<x-layout.app :title="__('Edit user')" :breadcrumbs="[['label' => __('Users & Access')], ['label' => __('Users'), 'url' => route('users.index')], ['label' => $user->name]]">
    <x-card :title="__('Edit :name', ['name' => $user->name])" class="max-w-4xl">
        <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            @include('core::users._form')
            <div class="flex flex-wrap gap-2">
                <x-button type="submit">{{ __('Save changes') }}</x-button>
                <x-button href="{{ route('users.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
                @can('delete', $user)
                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="ms-auto" onsubmit="return confirm('{{ __('Remove this user from the company?') }}')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">{{ __('Remove') }}</x-button>
                    </form>
                @endcan
            </div>
        </form>
    </x-card>
</x-layout.app>
