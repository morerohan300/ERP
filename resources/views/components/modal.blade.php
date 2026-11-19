@props(['name', 'title' => null, 'size' => 'md'])

@php
    $sizes = ['sm' => 'max-w-sm', 'md' => 'max-w-lg', 'lg' => 'max-w-2xl', 'xl' => 'max-w-4xl'];
@endphp

<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
     x-on:keydown.escape.window="open = false"
     x-cloak>
    @if (isset($trigger))
        <div {{ $trigger->attributes }} x-on:click="open = true">{{ $trigger }}</div>
    @endif

    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-show="open" role="dialog" aria-modal="true"
         x-transition.opacity @click.self="open = false">
        <div class="absolute inset-0 bg-navy-950/60"></div>
        <div class="relative w-full {{ $sizes[$size] ?? $sizes['md'] }} rounded-panel bg-white shadow-xl dark:bg-navy-900" x-transition.scale>
            @if ($title)
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-navy-800">
                    <h2 class="font-heading text-base text-navy-900 dark:text-white">{{ $title }}</h2>
                    <button type="button" class="rounded-control p-1 text-slate-400 hover:bg-slate-100" @click="open = false" aria-label="{{ __('Close') }}">✕</button>
                </div>
            @endif
            <div class="p-5">{{ $slot }}</div>
            @isset($footer)
                <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-3 dark:border-navy-800">{{ $footer }}</div>
            @endisset
        </div>
    </div>
</div>
