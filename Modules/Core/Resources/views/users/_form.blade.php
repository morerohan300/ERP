@php
    $pivot = $pivot ?? null;
    $assignedRoles = old('roles', isset($user) ? $user->roles->pluck('name')->all() : []);
    $assignedBranches = old('branch_ids', $pivot?->branch_ids ? json_decode($pivot->branch_ids, true) : []);
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="name" :label="__('Full name')" :value="$user->name ?? old('name')" required />
    @unless (isset($user) && $user)
        <x-form.input name="email" :label="__('Email address')" type="email" :value="old('email')" required />
    @endunless
    <x-form.input name="phone" :label="__('Phone')" :value="$user->phone ?? old('phone')" />
    <x-form.input name="job_title" :label="__('Job title')" :value="old('job_title', $pivot?->job_title)" />
    <x-form.input name="password" :label="__('Password')" type="password" :help="isset($user) ? __('Leave blank to keep the current password.') : __('Leave blank to email a setup link.')" autocomplete="new-password" />
    <x-form.input name="password_confirmation" :label="__('Confirm password')" type="password" autocomplete="new-password" />

    <x-form.select name="roles" :label="__('Roles')" :options="$roles" :value="$assignedRoles" multiple />
    <x-form.select name="branch_ids" :label="__('Branches')" :options="$branches" :value="$assignedBranches" multiple />
    <x-form.select name="department_id" :label="__('Department')" :options="$departments" :value="old('department_id', $pivot?->department_id)" placeholder="{{ __('No department') }}" />
    <x-form.select name="locale" :label="__('Language')" :options="collect(config('locales.supported'))->map(fn ($l) => $l['native'])" :value="$user->locale ?? old('locale', 'en')" />
    <x-form.input name="timezone" :label="__('Timezone')" :value="$user->timezone ?? old('timezone', config('app.timezone'))" />

    <div class="md:col-span-2 space-y-3">
        <x-form.input name="avatar" :label="__('Avatar')" type="file" accept="image/*" />
        <x-form.checkbox name="is_active" :label="__('Active')" :checked="$user->is_active ?? true" />
    </div>
</div>
