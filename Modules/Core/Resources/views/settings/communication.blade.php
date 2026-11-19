<x-layout.app :title="__('Email & SMS')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Email & SMS')]]">
    <form method="POST" action="{{ route('settings.communication.update') }}" class="grid gap-6 lg:grid-cols-2">
        @csrf
        @method('PUT')
        <x-card :title="__('Email')" :subtitle="__('Outgoing mail configuration')">
            <div class="space-y-4">
                <div>
                    <label for="mail_mailer" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Mailer') }}</label>
                    <select id="mail_mailer" name="settings[mail_mailer]" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                        @foreach (['log' => 'Log', 'smtp' => 'SMTP', 'array' => 'Array'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('settings.mail_mailer', $mail['mailer'] ?? 'log') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('settings.mail_mailer')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="mail_host" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Host') }}</label>
                        <input id="mail_host" name="settings[mail_host]" value="{{ old('settings.mail_host', $mail['host'] ?? '') }}" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                    </div>
                    <div>
                        <label for="mail_port" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Port') }}</label>
                        <input id="mail_port" type="number" name="settings[mail_port]" value="{{ old('settings.mail_port', $mail['port'] ?? '') }}" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                    </div>
                </div>
                <div>
                    <label for="mail_username" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Username') }}</label>
                    <input id="mail_username" name="settings[mail_username]" value="{{ old('settings.mail_username', $mail['username'] ?? '') }}" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                </div>
                <div>
                    <label for="mail_password" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Password') }}{{ $secrets['mail.password'] ? ' ('.__('configured').')' : '' }}</label>
                    <input id="mail_password" type="password" name="settings[mail_password]" value="" autocomplete="new-password" placeholder="{{ $secrets['mail.password'] ? __('Leave blank to keep current password') : '' }}" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="mail_from_address" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('From address') }}</label>
                        <input id="mail_from_address" type="email" name="settings[mail_from_address]" value="{{ old('settings.mail_from_address', $mail['from_address'] ?? '') }}" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                    </div>
                    <div>
                        <label for="mail_from_name" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('From name') }}</label>
                        <input id="mail_from_name" name="settings[mail_from_name]" value="{{ old('settings.mail_from_name', $mail['from_name'] ?? '') }}" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                    </div>
                </div>
            </div>
        </x-card>

        <x-card :title="__('SMS')" :subtitle="__('Notifications sent to mobile numbers')">
            <div class="space-y-4">
                <div>
                    <label for="sms_provider" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Provider') }}</label>
                    <input id="sms_provider" name="settings[sms_provider]" value="{{ old('settings.sms_provider', $sms['provider'] ?? '') }}" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                </div>
                <div>
                    <label for="sms_api_key" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('API key') }}{{ $secrets['sms.api_key'] ? ' ('.__('configured').')' : '' }}</label>
                    <input id="sms_api_key" type="password" name="settings[sms_api_key]" value="" autocomplete="new-password" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                </div>
                <div>
                    <label for="sms_api_secret" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('API secret') }}</label>
                    <input id="sms_api_secret" type="password" name="settings[sms_api_secret]" value="" autocomplete="new-password" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                </div>
                <x-button type="submit" class="mt-4">{{ __('Save settings') }}</x-button>
            </div>
        </x-card>
    </form>
</x-layout.app>
