@php
    $user = auth()->user();
@endphp

<x-layout.app :title="__('My profile')" :breadcrumbs="[['label' => __('My profile')]]">
    <div class="grid gap-6 lg:grid-cols-3">
        <x-card :title="__('Profile')" class="lg:col-span-2">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form.input name="name" :label="__('Full name')" :value="old('name', $user->name)" required />
                <x-form.input name="email" :label="__('Email address')" type="email" :value="old('email', $user->email)" required />
                <x-form.input name="phone" :label="__('Phone')" :value="old('phone', $user->phone)" />
                <x-form.select name="locale" :label="__('Language')" :options="collect(config('locales.supported'))->map(fn ($l) => $l['native'])" :value="old('locale', $user->locale)" />
                <x-form.input name="timezone" :label="__('Timezone')" :value="old('timezone', $user->timezone)" />
                <x-form.input name="avatar" :label="__('Avatar')" type="file" accept="image/*" />
                <x-button type="submit">{{ __('Save profile') }}</x-button>
            </form>
        </x-card>

        <div class="space-y-6">
            <x-card :title="__('Change password')">
                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <x-form.input name="current_password" :label="__('Current password')" type="password" required />
                    <x-form.input name="password" :label="__('New password')" type="password" required />
                    <x-form.input name="password_confirmation" :label="__('Confirm password')" type="password" required />
                    <x-button type="submit">{{ __('Update password') }}</x-button>
                </form>
            </x-card>

            @if (config('fortify.features') && in_array('twoFactorAuthentication', config('fortify.features'), true))
                <x-card :title="__('Two-factor authentication')">
                    @if ($user->two_factor_secret)
                        <p class="text-sm text-slate-500">{{ __('Two-factor authentication is enabled.') }}</p>
                        <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="secondary" size="sm">{{ __('Disable 2FA') }}</x-button>
                        </form>
                    @else
                        <p class="text-sm text-slate-500">{{ __('Protect your account with two-factor authentication.') }}</p>
                        <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-3">
                            @csrf
                            <x-button type="submit" size="sm">{{ __('Enable 2FA') }}</x-button>
                        </form>
                    @endif
                </x-card>
            @endif
        </div>
    </div>
</x-layout.app>
