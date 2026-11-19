<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $textDirection ?? 'ltr' }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Sign in')) · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ivory dark:bg-navy-950">
<div class="flex min-h-screen items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-3 font-heading text-2xl text-navy-900 dark:text-white">
                <span class="grid size-10 place-items-center rounded-panel bg-gold-500 font-sans text-base font-bold text-navy-900">L</span>
                {{ config('app.name') }}
            </a>
        </div>

        <div class="rounded-panel border border-slate-200 bg-white p-6 shadow-card dark:border-navy-800 dark:bg-navy-900">
            @yield('content')
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">© {{ now()->year }} {{ config('app.name') }}</p>
    </div>
</div>
</body>
</html>
