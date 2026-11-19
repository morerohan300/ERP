@php
    $selected = old('permissions', isset($role) ? $role->permissions->pluck('name')->all() : []);
@endphp

<x-form.input name="name" :label="__('Role name')" :value="$role->name ?? old('name')" required class="max-w-md" />

<div class="mt-6 space-y-6">
    @foreach ($matrix as $key => $group)
        <div>
            <h3 class="mb-2 font-heading text-sm text-navy-900 dark:text-white">{{ $group['module']->label }}</h3>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($group['entities'] as $entity => $actions)
                    <div class="rounded-control border border-slate-200 p-3 dark:border-navy-800">
                        <p class="mb-2 text-xs font-semibold tracking-wide text-slate-500 uppercase">{{ str_replace('_', ' ', $entity) }}</p>
                        <div class="space-y-1.5">
                            @foreach ($actions as $action => $permission)
                                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                           @checked(in_array($permission, $selected, true))
                                           class="rounded-control border-slate-300 text-gold-500 focus:ring-gold-500">
                                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
