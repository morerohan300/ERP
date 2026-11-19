<x-layout.app :title="__('Payment gateways')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Payment gateways')]]">
    <x-card :title="__('Payment gateways')" :subtitle="__('Credentials are encrypted at rest.')">
        <form method="POST" action="{{ route('settings.payments.update') }}" class="space-y-6">
            @csrf
            @method('PUT')
            @foreach ([
                'stripe' => __('Stripe'),
                'paypal' => __('PayPal'),
                'razorpay' => __('Razorpay'),
            ] as $gateway => $label)
                @php $enabled = 'settings.'.$gateway.'_enabled'; @endphp
                <div class="rounded-control border border-slate-200 p-4 dark:border-navy-800">
                    <label class="flex items-center gap-2 text-sm font-medium text-navy-900 dark:text-white">
                        <input type="hidden" name="settings[{{ $gateway }}_enabled]" value="0">
                        <input type="checkbox" name="settings[{{ $gateway }}_enabled]" value="1" @checked(old($enabled, (bool) ($settings[$gateway.'_enabled'] ?? false)))
                               class="rounded-control border-slate-300 text-gold-500 focus:ring-gold-500">
                        {{ $label }}
                    </label>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        @foreach (($gateway === 'stripe' ? ['key' => __('Publishable key'), 'secret' => __('Secret key'), 'webhook_secret' => __('Webhook secret')] : ($gateway === 'paypal' ? ['client_id' => __('Client ID'), 'secret' => __('Secret')] : ['key' => __('Key ID'), 'secret' => __('Secret')])) as $field => $fieldLabel)
                            @php $key = $gateway.'_'.$field; @endphp
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ $fieldLabel }}</label>
                                <input type="password" name="settings[{{ $key }}]" value="" autocomplete="new-password" placeholder="@if (! empty($settings[$key])){{ __('Leave blank to keep current value') }}@endif" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <x-button type="submit">{{ __('Save payment settings') }}</x-button>
        </form>
    </x-card>
</x-layout.app>
