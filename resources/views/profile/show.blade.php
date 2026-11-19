<x-layout.app :title="__('Profile & security')" :breadcrumbs="[['label' => __('Profile')]]">
    <div class="mx-auto max-w-4xl space-y-6">
        <x-card :title="__('Profile information')" :subtitle="__('Update your name and email address.')">
            <form method="POST" action="{{ route('user-profile-information.update') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                @method('PUT')
                <x-form.input name="name" :label="__('Full name')" :value="old('name', auth()->user()->name)" required />
                <x-form.input name="email" :label="__('Email address')" type="email" :value="old('email', auth()->user()->email)" required />
                <div class="md:col-span-2">
                    @if (session('status') === 'profile-information-updated')
                        <p class="mb-3 text-sm text-success">{{ __('Profile updated.') }}</p>
                    @endif
                    <x-button type="submit">{{ __('Save changes') }}</x-button>
                </div>
            </form>
        </x-card>

        <x-card :title="__('Change password')" :subtitle="__('Use a long, unique password you do not use elsewhere.')">
            <form method="POST" action="{{ route('user-password.update') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                @method('PUT')
                <x-form.input name="current_password" :label="__('Current password')" type="password" required autocomplete="current-password" />
                <div>
                    <x-form.input name="password" :label="__('New password')" type="password" required autocomplete="new-password" />
                </div>
                <x-form.input name="password_confirmation" :label="__('Confirm new password')" type="password" required autocomplete="new-password" />
                <div class="md:col-span-2">
                    @if (session('status') === 'password-updated')
                        <p class="mb-3 text-sm text-success">{{ __('Password updated.') }}</p>
                    @endif
                    <x-button type="submit">{{ __('Update password') }}</x-button>
                </div>
            </form>
        </x-card>

        <x-card :title="__('Two-factor authentication')"
                :subtitle="__('Protect your account with an authenticator app.')"
                x-data="{
                    enabled: @js(auth()->user()->hasTwoFactorEnabled()),
                    pending: @js(auth()->user()->two_factor_secret !== null && ! auth()->user()->hasTwoFactorEnabled()),
                    qr: '', secret: '', codes: [],
                    async load() {
                        if (! this.pending) return;
                        const qr = await fetch('{{ route('two-factor.qr-code') }}').then(r => r.json());
                        this.qr = qr.svg ?? '';
                        const secret = await fetch('{{ route('two-factor.secret-key') }}').then(r => r.json());
                        this.secret = secret.secret ?? '';
                    }
                }"
                x-init="load()">
            @if (session('status') === 'two-factor-authentication-enabled')
                <p class="mb-4 text-sm text-success">{{ __('Scan the QR code below, then confirm with a code from your app.') }}</p>
            @endif
            @if (session('status') === 'two-factor-authentication-confirmed')
                <p class="mb-4 text-sm text-success">{{ __('Two-factor authentication is now enabled.') }}</p>
            @endif
            @if (session('status') === 'two-factor-authentication-disabled')
                <p class="mb-4 text-sm text-success">{{ __('Two-factor authentication has been disabled.') }}</p>
            @endif

            <div x-show="! enabled && ! pending">
                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <x-button variant="secondary">{{ __('Enable two-factor authentication') }}</x-button>
                </form>
            </div>

            <div x-show="pending" x-cloak class="space-y-4">
                <div class="flex flex-wrap items-start gap-6">
                    <div class="rounded-panel border border-slate-200 bg-white p-2 dark:border-navy-800" x-html="qr"></div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase">{{ __('Secret key') }}</p>
                        <code class="mt-1 block break-all text-sm" x-text="secret"></code>
                    </div>
                </div>
                <form method="POST" action="{{ route('two-factor.confirm') }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="w-48">
                        <x-form.input name="code" :label="__('Confirmation code')" inputmode="numeric" autocomplete="one-time-code" />
                    </div>
                    <x-button type="submit">{{ __('Confirm and enable') }}</x-button>
                </form>
            </div>

            <div x-show="enabled" x-cloak class="space-y-4">
                <p class="flex items-center gap-2 text-sm text-success">
                    <span aria-hidden="true">✓</span> {{ __('Two-factor authentication is enabled.') }}
                </p>
                <div>
                    <button type="button" class="text-sm text-gold-600 hover:underline"
                            x-on:click="codes = await fetch('{{ route('two-factor.recovery-codes') }}').then(r => r.json())">
                        {{ __('Show recovery codes') }}
                    </button>
                    <ul x-show="codes.length" x-cloak class="mt-2 grid gap-1 rounded-panel bg-slate-50 p-3 font-mono text-xs dark:bg-navy-950">
                        <template x-for="code in codes" :key="code"><li x-text="code"></li></template>
                    </ul>
                    <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="text-xs text-slate-500 hover:underline">{{ __('Regenerate recovery codes') }}</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('two-factor.disable') }}">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" size="sm">{{ __('Disable two-factor authentication') }}</x-button>
                </form>
            </div>
        </x-card>
    </div>
</x-layout.app>
