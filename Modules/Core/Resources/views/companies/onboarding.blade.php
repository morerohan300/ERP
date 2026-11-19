<x-layout.app :title="__('Set up your company')" :breadcrumbs="[]">
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="text-center">
            <h2 class="font-heading text-2xl text-navy-900 dark:text-white">{{ __('Welcome to Ledgerline') }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ __('Create your first company to start using the workspace.') }}</p>
        </div>

        <x-card :title="__('Company details')">
            <form method="POST" action="{{ route('companies.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @include('core::companies._form')
                <div class="flex justify-end">
                    <x-button type="submit">{{ __('Create company') }}</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layout.app>
