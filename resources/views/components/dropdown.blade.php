@props(['label'])

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" class="inline-flex items-center gap-2 rounded-control border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-navy-800 dark:bg-navy-900 dark:text-slate-200"
            @click="open = !open" :aria-expanded="open" aria-haspopup="true">
        <span class="max-w-40 truncate">{{ $label }}</span>
        <span class="text-xs" aria-hidden="true">▾</span>
    </button>
    <div class="absolute end-0 z-50 mt-1 min-w-56 rounded-panel border border-slate-200 bg-white py-1 shadow-card dark:border-navy-800 dark:bg-navy-900"
         x-show="open" x-cloak x-transition.opacity>
        {{ $slot }}
    </div>
</div>
