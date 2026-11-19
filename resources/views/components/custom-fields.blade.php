@props(['model' => null, 'entity' => null])

@php
    $entityType = $entity ?? $model?->getMorphClass();
    $fields = $entityType ? app(\Modules\Core\Services\CustomFieldService::class)->fieldsFor((string) $entityType) : collect();
    $data = $model?->customFieldData() ?? [];
@endphp

@if ($fields->isNotEmpty())
    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($fields as $field)
            @php $name = "custom_fields.{$field->name}"; $value = old($name, $data[$field->name] ?? $field->default_value); @endphp
            <div class="{{ $field->type === 'textarea' ? 'md:col-span-2' : '' }}">
                @if ($field->type !== 'checkbox')
                    <label for="cf-{{ $field->name }}" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">
                        {{ $field->label }} @if ($field->is_required)<span class="text-danger">*</span>@endif
                    </label>
                @endif

                @switch($field->type)
                    @case('textarea')
                        <textarea id="cf-{{ $field->name }}" name="{{ $name }}" rows="3" class="w-full rounded-control border-slate-300 text-sm dark:border-navy-700 dark:bg-navy-950">{{ $value }}</textarea>
                        @break
                    @case('select')
                        <select id="cf-{{ $field->name }}" name="{{ $name }}" class="w-full rounded-control border-slate-300 text-sm dark:border-navy-700 dark:bg-navy-950">
                            <option value="">—</option>
                            @foreach ($field->options ?? [] as $option)
                                <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @break
                    @case('multiselect')
                        <select id="cf-{{ $field->name }}" name="{{ $name }}[]" multiple class="w-full rounded-control border-slate-300 text-sm dark:border-navy-700 dark:bg-navy-950">
                            @foreach ($field->options ?? [] as $option)
                                <option value="{{ $option }}" @selected(is_array($value) && in_array($option, $value, true))>{{ $option }}</option>
                            @endforeach
                        </select>
                        @break
                    @case('checkbox')
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input id="cf-{{ $field->name }}" type="checkbox" name="{{ $name }}" value="1" @checked((bool) $value) class="rounded-control border-slate-300 text-gold-500">
                            {{ $field->label }}
                        </label>
                        @break
                    @default
                        <input id="cf-{{ $field->name }}" type="{{ $field->type }}" name="{{ $name }}" value="{{ $value }}"
                               class="w-full rounded-control border-slate-300 text-sm dark:border-navy-700 dark:bg-navy-950">
                @endswitch

                @if ($field->help_text)<p class="mt-1 text-xs text-slate-400">{{ $field->help_text }}</p>@endif
                @error($name)<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
@endif
