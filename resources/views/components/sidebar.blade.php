@props(['menu' => []])

@php
    $sections = ['main' => __('Workspace'), 'admin' => __('Administration')];
@endphp

{{-- Desktop rail --}}
<aside class="hidden w-64 shrink-0 flex-col bg-navy-900 text-slate-200 lg:flex" aria-label="{{ __('Main navigation') }}">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-5 py-5 font-heading text-lg text-white">
        <span class="grid size-9 place-items-center rounded-panel bg-gold-500 font-sans text-sm font-bold text-navy-900">L</span>
        <span>{{ config('app.name') }}</span>
    </a>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 pb-4">
        @foreach ($sections as $key => $label)
            @continue(empty($menu[$key]))
            <div>
                <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">{{ $label }}</p>
                <ul class="space-y-1">
                    @foreach ($menu[$key] as $item)
                        <li>
                            @if (isset($item['children']))
                                <details class="group" {{ request()->routeIs(data_get(array_first($item['children']), 'route', 'none').'*') ? 'open' : '' }}>
                                    <summary class="flex cursor-pointer items-center gap-3 rounded-control px-3 py-2 text-sm hover:bg-navy-800">
                                        <span class="size-1.5 rounded-full bg-gold-500"></span>
                                        <span class="flex-1">{{ __($item['label']) }}</span>
                                        <span class="text-xs transition group-open:rotate-90">›</span>
                                    </summary>
                                    <ul class="mt-1 space-y-0.5 ps-4">
                                        @foreach ($item['children'] as $child)
                                            <li>
                                                <a href="{{ route($child['route']) }}"
                                                   class="block rounded-control px-3 py-1.5 text-sm text-slate-300 hover:bg-navy-800 hover:text-white {{ request()->routeIs($child['route'].'*') ? 'bg-navy-800 text-white' : '' }}">
                                                    {{ __($child['label']) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @else
                                <a href="{{ route($item['route']) }}"
                                   class="flex items-center gap-3 rounded-control px-3 py-2 text-sm hover:bg-navy-800 hover:text-white {{ request()->routeIs(($item['route'] ?? '').'*') ? 'bg-navy-800 text-white' : '' }}">
                                    <span class="size-1.5 rounded-full bg-gold-500"></span>
                                    {{ __($item['label']) }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>
</aside>

{{-- Mobile drawer --}}
<div class="fixed inset-0 z-40 lg:hidden" x-show="sidebarOpen" x-cloak>
    <div class="absolute inset-0 bg-navy-950/60" @click="sidebarOpen = false"></div>
    <aside class="absolute inset-y-0 start-0 w-72 overflow-y-auto bg-navy-900 p-4 text-slate-200">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="font-heading text-lg text-white">{{ config('app.name') }}</a>
            <button type="button" class="rounded-control p-2 hover:bg-navy-800" @click="sidebarOpen = false" aria-label="{{ __('Close menu') }}">✕</button>
        </div>
        <nav class="space-y-4">
            @foreach ($menu as $sectionItems)
                <ul class="space-y-1">
                    @foreach ($sectionItems as $item)
                        <li>
                            @if (isset($item['children']))
                                <p class="px-3 py-2 text-xs text-slate-400 uppercase">{{ __($item['label']) }}</p>
                                <ul class="ps-3">
                                    @foreach ($item['children'] as $child)
                                        <li><a href="{{ route($child['route']) }}" class="block rounded-control px-3 py-2 text-sm hover:bg-navy-800">{{ __($child['label']) }}</a></li>
                                    @endforeach
                                </ul>
                            @else
                                <a href="{{ route($item['route']) }}" class="block rounded-control px-3 py-2 text-sm hover:bg-navy-800">{{ __($item['label']) }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </nav>
    </aside>
</div>
