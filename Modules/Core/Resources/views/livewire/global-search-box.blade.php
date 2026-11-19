<div class="relative hidden sm:block" x-data="{ open: @js($open) }" x-on:keydown.escape.window="open = false; $wire.close()">
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400" aria-hidden="true">⌕</span>
        <input type="search" wire:model.live.debounce.250ms="term"
               @focus="open = $wire.term.length >= 2"
               placeholder="{{ __('Search…') }}" aria-label="{{ __('Global search') }}"
               class="w-56 rounded-control border-slate-200 bg-slate-50 py-2 ps-9 pe-3 text-sm shadow-sm focus:border-gold-500 focus:bg-white focus:ring-gold-500 dark:border-navy-800 dark:bg-navy-900 dark:text-white lg:w-72">
    </div>

    @if ($open)
        <div class="absolute end-0 z-50 mt-1 max-h-[70vh] w-96 max-w-[calc(100vw-2rem)] overflow-y-auto rounded-panel border border-slate-200 bg-white py-1 shadow-card dark:border-navy-800 dark:bg-navy-900"
             x-show="open" x-cloak>
            @forelse ($results as $group => $items)
                <div class="border-b border-slate-100 py-1 last:border-0 dark:border-navy-800">
                    <p class="px-3 py-1 text-xs font-semibold tracking-wide text-slate-400 uppercase">{{ $group }}</p>
                    @foreach ($items as $item)
                        <a href="{{ $item['url'] }}" wire:click="close" class="block px-3 py-2 hover:bg-slate-50 dark:hover:bg-navy-800">
                            <p class="text-sm text-navy-900 dark:text-white">{{ $item['title'] }}</p>
                            @if ($item['subtitle'])<p class="text-xs text-slate-500">{{ $item['subtitle'] }}</p>@endif
                        </a>
                    @endforeach
                </div>
            @empty
                <p class="px-3 py-4 text-center text-sm text-slate-500">{{ __('No results found.') }}</p>
            @endforelse
        </div>
    @endif
</div>
