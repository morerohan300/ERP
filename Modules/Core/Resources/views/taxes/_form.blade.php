<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="name" :label="__('Tax name')" :value="$tax->name ?? old('name')" required />
    <x-form.input name="code" :label="__('Code')" :value="$tax->code ?? old('code')" required />
    <x-form.input name="rate" :label="__('Rate')" type="number" step="any" :value="$tax->rate ?? old('rate', 0)" required />
    <x-form.select name="type" :label="__('Type')" :options="['percent' => __('Percentage'), 'fixed' => __('Fixed amount')]" :value="$tax->type ?? old('type', 'percent')" required />
    <x-form.select name="scope" :label="__('Scope')" :options="['sales' => __('Sales'), 'purchase' => __('Purchase'), 'both' => __('Both')]" :value="$tax->scope ?? old('scope', 'both')" required />
    <x-form.textarea name="description" :label="__('Description')" :value="$tax->description ?? old('description')" />
    <div class="md:col-span-2 flex flex-wrap gap-6">
        <x-form.checkbox name="is_compound" :label="__('Compound tax')" :checked="$tax->is_compound ?? false" />
        <x-form.checkbox name="is_inclusive" :label="__('Price inclusive')" :checked="$tax->is_inclusive ?? false" />
        <x-form.checkbox name="is_active" :label="__('Active')" :checked="$tax->is_active ?? true" />
    </div>
</div>
