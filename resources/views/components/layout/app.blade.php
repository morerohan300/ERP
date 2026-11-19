@props(['title' => null])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $textDirection ?? 'ltr' }}"
      class="h-full {{ $darkMode ?? '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full">
<a href="#main-content"
   class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-100 focus:rounded-control focus:bg-navy-900 focus:px-4 focus:py-2 focus:text-white">
    {{ __('Skip to content') }}
</a>

<div class="flex min-h-screen" x-data="{ sidebarOpen: false }">
    <x-sidebar :menu="$menu" />

    <div class="flex min-w-0 flex-1 flex-col">
        <x-header :title="$title" />

        <main id="main-content" class="flex-1 p-4 lg:p-6">
            <x-breadcrumbs :items="$breadcrumbs" />
            {{ $slot }}
        </main>

        <footer class="px-4 pb-4 text-xs text-slate-400 lg:px-6">
            {{ config('app.name') }} · {{ now()->year }}
        </footer>
    </div>
</div>

<x-toast />
@stack('scripts')
</body>
</html>
