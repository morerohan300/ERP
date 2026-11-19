@props(['label' => null, 'name', 'required' => false, 'help' => null, 'accept' => null])

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">
            {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
        </label>
    @endif
    <input id="{{ $name }}" type="file" name="{{ $name }}" @if ($accept) accept="{{ $accept }}" @endif
           {{ $attributes->merge(['class' => 'block w-full text-sm text-slate-600 file:me-3 file:rounded-control file:border-0 file:bg-navy-900 file:px-3 file:py-2 file:text-white dark:text-slate-300']) }}>
    @if ($help)<p class="mt-1 text-xs text-slate-400">{{ $help }}</p>@endif
    @error($name)<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
</div>
