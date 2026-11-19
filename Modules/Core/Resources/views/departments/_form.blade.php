<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="name" :label="__('Department name')" :value="$department->name ?? old('name')" required />
    <x-form.input name="code" :label="__('Code')" :value="$department->code ?? old('code')" />
    <x-form.select name="branch_id" :label="__('Branch')" :options="$branches" :value="$department->branch_id ?? old('branch_id')" placeholder="{{ __('No branch') }}" />
    <x-form.select name="parent_id" :label="__('Parent department')" :options="$parents" :value="$department->parent_id ?? old('parent_id')" placeholder="{{ __('None') }}" />
    <x-form.select name="manager_id" :label="__('Manager')" :options="$managers" :value="$department->manager_id ?? old('manager_id')" placeholder="{{ __('No manager') }}" />
    <x-form.textarea name="description" :label="__('Description')" :value="$department->description ?? old('description')" />
</div>
