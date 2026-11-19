@props(['title' => null])

<header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur dark:border-navy-800 dark:bg-navy-950/95 lg:px-6">
    <button type="button" class="rounded-control p-2 hover:bg-slate-100 dark:hover:bg-navy-800 lg:hidden" @click="sidebarOpen = true" aria-label="{{ __('Open menu') }}">
        <span class="text-lg">☰</span>
    </button>

    <h1 class="truncate font-heading text-base text-navy-900 dark:text-white lg:text-lg">{{ $title ?? config('app.name') }}</h1>

    <div class="ms-auto flex items-center gap-2">
        <livewire:core.global-search />

        @if ($currentCompany ?? null)
            <x-dropdown :label="$currentCompany->name">
                @foreach (auth()->user()->companies as $company)
                    <form method="POST" action="{{ route('companies.switch', $company) }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-start text-sm hover:bg-slate-50 dark:hover:bg-navy-800 {{ $company->is($currentCompany) ? 'text-gold-600' : '' }}">
                            {{ $company->name }}
                            @if ($company->is($currentCompany)) <span aria-hidden="true">✓</span> @endif
                        </button>
                    </form>
                @endforeach
                @can('core.company.view')
                    <a href="{{ route('settings.company') }}" class="block border-t border-slate-100 px-3 py-2 text-sm hover:bg-slate-50 dark:border-navy-800 dark:hover:bg-navy-800">{{ __('Company profile') }}</a>
                @endcan
            </x-dropdown>
        @endif

        <livewire:core.notifications-dropdown />

        <button type="button"
                class="rounded-control p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-navy-800"
                x-on:click="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')"
                aria-label="{{ __('Toggle dark mode') }}">◐</button>

        <x-dropdown :label="auth()->user()->initials()">
            <div class="border-b border-slate-100 px-3 py-2 text-sm dark:border-navy-800">
                <p class="font-medium">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
            </div>
            <a href="{{ route('profile.show') }}" class="block px-3 py-2 text-sm hover:bg-slate-50 dark:hover:bg-navy-800">{{ __('Profile & security') }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full px-3 py-2 text-start text-sm text-danger hover:bg-slate-50 dark:hover:bg-navy-800">{{ __('Log out') }}</button>
            </form>
        </x-dropdown>
    </div>
</header>
