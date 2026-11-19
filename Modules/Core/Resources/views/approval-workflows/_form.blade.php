@php
    $steps = old('steps', isset($workflow)
        ? $workflow->steps->sortBy('step_order')->map(fn ($step) => [
            'name' => $step->name,
            'approver_type' => $step->approver_type,
            'approver_id' => $step->approver_id,
            'min_approvals' => $step->min_approvals,
        ])->values()->all()
        : [['name' => __('Manager approval'), 'approver_type' => 'role', 'approver_id' => '', 'min_approvals' => 1]]);
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <x-form.select name="document_type" :label="__('Document type')" :options="array_combine($documentTypes, array_map(fn ($type) => str_replace(['_', '-'], ' ', ucfirst($type)), $documentTypes))" :value="$workflow->document_type ?? old('document_type')" required />
    <x-form.input name="name" :label="__('Workflow name')" :value="$workflow->name ?? old('name')" required />
    <x-form.input name="priority" :label="__('Priority')" type="number" :value="$workflow->priority ?? old('priority', 0)" />
    <x-form.textarea name="description" :label="__('Description')" :value="$workflow->description ?? old('description')" />
    <x-form.checkbox name="is_active" :label="__('Active')" :checked="$workflow->is_active ?? true" />
</div>

<div class="mt-6" x-data="{ steps: @js($steps) }">
    <div class="mb-2 flex items-center justify-between">
        <h3 class="font-heading text-sm text-navy-900 dark:text-white">{{ __('Approval steps') }}</h3>
        <button type="button" class="text-xs text-gold-600 hover:underline" @click="steps.push({ name: '', approver_type: 'role', approver_id: '', min_approvals: 1 })">+ {{ __('Add step') }}</button>
    </div>

    <template x-for="(step, i) in steps" :key="i">
        <div class="mb-3 grid gap-3 rounded-control border border-slate-200 p-3 md:grid-cols-4 dark:border-navy-800">
            <div>
                <label class="mb-1 block text-xs text-slate-500">{{ __('Step name') }}</label>
                <input type="text" :name="'steps['+i+'][name]'" x-model="step.name" required class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-500">{{ __('Approver type') }}</label>
                <select :name="'steps['+i+'][approver_type]'" x-model="step.approver_type" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                    <option value="role">{{ __('Role') }}</option>
                    <option value="user">{{ __('User') }}</option>
                    <option value="department_manager">{{ __('Department manager') }}</option>
                </select>
            </div>
            <div x-show="step.approver_type !== 'department_manager'">
                <label class="mb-1 block text-xs text-slate-500">{{ __('Approver') }}</label>
                <select :name="'steps['+i+'][approver_id]'" x-model="step.approver_id" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                    <option value="">{{ __('Select…') }}</option>
                    <template x-if="step.approver_type === 'role'">
                        @foreach ($roles as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </template>
                    <template x-if="step.approver_type === 'user'">
                        @foreach ($users as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </template>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-500">{{ __('Minimum approvals') }}</label>
                <div class="flex gap-2">
                    <input type="number" min="1" :name="'steps['+i+'][min_approvals]'" x-model="step.min_approvals" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                    <button type="button" class="text-xs text-danger hover:underline" @click="steps.splice(i, 1)">×</button>
                </div>
            </div>
        </div>
    </template>
</div>
