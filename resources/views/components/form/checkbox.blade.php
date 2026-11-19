@props(['label', 'name', 'value' => 1, 'checked' => false, 'help' => null])

<div>
    <label class="inline-flex items-start gap-2 text-sm text-slate-700 dark:text-slate-200">
        <input type="hidden" name="{{ $name }}" value="0">
        <input id="{{ $name }}" type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked(old($name, $checked))
               {{ $attributes->merge(['class' => 'mt-0.5 rounded-control border-slate-300 text-gold-500 focus:ring-gold-500']) }}>
        <span>{{ $label }}</span>
    </label>
    @if ($help)<p class="mt-1 text-xs text-slate-400">{{ $help }}</p>@endif
    @error($name)<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
</div>
