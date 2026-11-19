@props(['name', 'title' => null])

<div x-data="{ open: false }"
     x-on:open-slide-over.window="if ($event.detail === '{{ $name }}') open = true"
     x-on:close-slide-over.window="if ($event.detail === '{{ $name }}') open = false"
     x-on:keydown.escape.window="open = false"
     x-cloak>
    @if (isset($trigger))
        <div {{ $trigger->attributes }} x-on:click="open = true">{{ $trigger }}</div>
    @endif

    <div class="fixed inset-0 z-50" x-show="open" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-navy-950/50" @click="open = false" x-transition.opacity></div>
        <div class="absolute inset-y-0 end-0 w-full max-w-md overflow-y-auto bg-white p-5 shadow-xl dark:bg-navy-900" x-transition:enter="transition duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-heading text-base text-navy-900 dark:text-white">{{ $title }}</h2>
                <button type="button" class="rounded-control p-1 text-slate-400 hover:bg-slate-100" @click="open = false" aria-label="{{ __('Close') }}">✕</button>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
