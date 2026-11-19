@props(['title' => null, 'subtitle' => null])

<section {{ $attributes->merge(['class' => 'rounded-panel border border-slate-200 bg-white shadow-card dark:border-navy-800 dark:bg-navy-900']) }}>
    @if ($title || isset($header))
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-navy-800">
            <div>
                <h2 class="font-heading text-base text-navy-900 dark:text-white">{{ $title }}</h2>
                @if ($subtitle)<p class="mt-1 text-xs text-slate-500">{{ $subtitle }}</p>@endif
            </div>
            {{ $header ?? '' }}
        </div>
    @endif

    <div class="p-5">{{ $slot }}</div>

    @isset($footer)
        <div class="border-t border-slate-100 px-5 py-3 dark:border-navy-800">{{ $footer }}</div>
    @endisset
</section>
