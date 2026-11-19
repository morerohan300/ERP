@php
    $field = $field ?? null;
@endphp
<div class="grid gap-4 md:grid-cols-2">
    <x-form.select name="entity_type" :label="__('Entity')" :options="$entities" :value="$field->entity_type ?? old('entity_type')" required />
    <x-form.input name="name" :label="__('Field name')" :value="$field->name ?? old('name')" required help="{{ __('Lowercase letters, numbers and underscores only.') }}" />
    <x-form.input name="label" :label="__('Label')" :value="$field->label ?? old('label')" required />
    <x-form.select name="type" :label="__('Type')" :options="array_combine($types, array_map('ucfirst', $types))" :value="$field->type ?? old('type', 'text')" required />
    <x-form.input name="options" :label="__('Options')" :value="old('options', isset($field->options) ? implode(', ', $field->options) : '')" help="{{ __('Comma-separated values for select and multiselect fields.') }}" />
    <x-form.input name="default_value" :label="__('Default value')" :value="$field->default_value ?? old('default_value')" />
    <x-form.input name="help_text" :label="__('Help text')" :value="$field->help_text ?? old('help_text')" />
    <x-form.input name="sort_order" :label="__('Sort order')" type="number" :value="$field->sort_order ?? old('sort_order', 0)" />
    <div class="md:col-span-2 flex flex-wrap gap-6">
        <x-form.checkbox name="is_required" :label="__('Required')" :checked="$field->is_required ?? false" />
        <x-form.checkbox name="show_in_table" :label="__('Show in table')" :checked="$field->show_in_table ?? false" />
        <x-form.checkbox name="is_active" :label="__('Active')" :checked="$field->is_active ?? true" />
    </div>
</div>
