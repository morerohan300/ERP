@props(['label' => null, 'name', 'type' => 'text', 'value' => null, 'required' => false, 'help' => null, 'placeholder' => null])

<div {{ $attributes->only(['class']) }}>
    @if ($label)
        <label for="{{ $name }}" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">
            {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
        </label>
    @endif
    <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}"
           placeholder="{{ $placeholder }}"
           {{ $attributes->except(['class'])->merge(['class' => 'w-full rounded-control border-slate-300 bg-white text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white']) }}>
    @if ($help)<p class="mt-1 text-xs text-slate-400">{{ $help }}</p>@endif
    @error($name)<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
</div>
