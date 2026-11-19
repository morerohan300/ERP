@props(['label', 'value', 'icon' => null, 'hint' => null, 'color' => 'navy'])

@php
    $colors = [
        'navy' => 'bg-navy-900/5 text-navy-900 dark:bg-white/5 dark:text-white',
        'gold' => 'bg-gold-500/10 text-gold-600',
        'success' => 'bg-success/10 text-success',
        'warning' => 'bg-warning/10 text-warning',
        'danger' => 'bg-danger/10 text-danger',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-panel border border-slate-200 bg-white p-5 shadow-card dark:border-navy-800 dark:bg-navy-900']) }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs font-medium tracking-wide text-slate-500 uppercase">{{ $label }}</p>
            <p class="mt-2 font-heading text-2xl text-navy-900 dark:text-white">{{ $value }}</p>
            @if ($hint)<p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>@endif
        </div>
        @if ($icon)
            <span class="grid size-10 place-items-center rounded-panel {{ $colors[$color] ?? $colors['navy'] }}" aria-hidden="true">{{ $icon }}</span>
        @endif
    </div>
    {{ $slot }}
</div>
