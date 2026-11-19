@props(['label' => null, 'name', 'options' => [], 'value' => null, 'required' => false, 'placeholder' => null, 'multiple' => false])

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-303">
            {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
        </label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}{{ $multiple ? '[]' : '' }}" @if ($multiple) multiple @endif
            {{ $attributes->merge(['class' => 'w-full rounded-control border-slate-300 bg-white text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white']) }}>
        @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $key => $option)
            @php $optionValue = is_array($option) ? $option['value'] : $key; $optionLabel = is_array($option) ? $option['label'] : $option; @endphp
            <option value="{{ $optionValue }}"
                    @if ($multiple && is_array(old($name, $value))) @selected(in_array($optionValue, old($name, $value), true)) @else @selected((string) old($name, $value) === (string) $optionValue) @endif>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
    @error($name)<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
</div>
