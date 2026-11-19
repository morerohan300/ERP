@props(['color' => 'slate', 'dot' => false])

@php
    $colors = [
        'slate' => 'bg-slate-100 text-slate-700 dark:bg-navy-800 dark:text-slate-200',
        'navy' => 'bg-navy-900/10 text-navy-900 dark:bg-white/10 dark:text-white',
        'gold' => 'bg-gold-500/15 text-gold-600',
        'success' => 'bg-success/10 text-success',
        'warning' => 'bg-warning/10 text-warning',
        'danger' => 'bg-danger/10 text-danger',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium '.($colors[$color] ?? $colors['slate'])]) }}>
    @if ($dot)<span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>@endif
    {{ $slot }}
</span>
