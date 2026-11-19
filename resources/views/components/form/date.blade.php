@props(['label' => null, 'name', 'value' => null, 'required' => false])

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">
            {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
        </label>
    @endif
    <input id="{{ $name }}" type="date" name="{{ $name }}" value="{{ old($name, $value) }}"
           {{ $attributes->merge(['class' => 'w-full rounded-control border-slate-300 bg-white text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white']) }}>
    @error($name)<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
</div>
