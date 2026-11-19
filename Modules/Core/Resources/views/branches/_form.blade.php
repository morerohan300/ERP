<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="name" :label="__('Branch name')" :value="$branch->name ?? old('name')" required />
    <x-form.input name="code" :label="__('Code')" :value="$branch->code ?? old('code')" required />
    <x-form.input name="email" :label="__('Email')" type="email" :value="$branch->email ?? old('email')" />
    <x-form.input name="phone" :label="__('Phone')" :value="$branch->phone ?? old('phone')" />

    <x-form.input name="address[line1]" :label="__('Address line 1')" :value="old('address.line1', $branch->address['line1'] ?? null)" />
    <x-form.input name="address[city]" :label="__('City')" :value="old('address.city', $branch->address['city'] ?? null)" />
    <x-form.input name="address[state]" :label="__('State / province')" :value="old('address.state', $branch->address['state'] ?? null)" />
    <x-form.input name="address[postal_code]" :label="__('Postal code')" :value="old('address.postal_code', $branch->address['postal_code'] ?? null)" />

    <div class="md:col-span-2 flex flex-wrap gap-6">
        <x-form.checkbox name="is_head_office" :label="__('This is the head office')" :checked="$branch->is_head_office ?? false" />
        <x-form.checkbox name="is_active" :label="__('Active')" :checked="$branch->is_active ?? true" />
    </div>
</div>
