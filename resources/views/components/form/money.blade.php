@props(['label' => null, 'name', 'value' => null, 'currency' => 'USD', 'required' => false])

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">
            {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
        </label>
    @endif
    <div class="relative">
        <input id="{{ $name }}" type="number" step="0.0001" name="{{ $name }}" value="{{ old($name, $value) }}"
               {{ $attributes->merge(['class' => 'w-full rounded-control border-slate-300 bg-white py-2 ps-3 pe-16 text-sm tabular-nums shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white']) }}>
        <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-xs text-slate-400">{{ $currency }}</span>
    </div>
    @error($name)<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
</div>
