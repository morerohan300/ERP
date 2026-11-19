@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
    $variants = [
        'primary' => 'bg-navy-900 text-white hover:bg-navy-800 dark:bg-gold-500 dark:text-navy-950 dark:hover:bg-gold-400',
        'accent' => 'bg-gold-500 text-navy-950 hover:bg-gold-600',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-navy-700 dark:bg-navy-900 dark:text-slate-200 dark:hover:bg-navy-800',
        'danger' => 'bg-danger text-white hover:brightness-90',
        'ghost' => 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-navy-800',
    ];
    $sizes = ['sm' => 'px-2.5 py-1.5 text-xs', 'md' => 'px-4 py-2 text-sm', 'lg' => 'px-5 py-2.5 text-sm'];
    $classes = 'inline-flex items-center justify-center gap-2 rounded-control font-medium transition disabled:opacity-50 '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
