<x-layout.app :title="__('General settings')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('General')]]">
    <x-card :title="__('General settings')" :subtitle="__('Defaults applied to this company.')" class="max-w-3xl">
        <form method="POST" action="{{ route('settings.general.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="date_format" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Date format') }}</label>
                    <select id="date_format" name="settings[date_format]" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                        @foreach ($dateFormats as $format => $example)
                            <option value="{{ $format }}" @selected(old('settings.date_format', $settings['date_format'] ?? 'd M Y') === $format)>{{ $example }}</option>
                        @endforeach
                    </select>
                    @error('settings.date_format')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="locale" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Language') }}</label>
                    <select id="locale" name="settings[locale]" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                        @foreach ($locales as $code => $language)
                            <option value="{{ $code }}" @selected(old('settings.locale', $settings['locale'] ?? 'en') === $code)>{{ $language['native'] }}</option>
                        @endforeach
                    </select>
                    @error('settings.locale')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="timezone" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Timezone') }}</label>
                    <select id="timezone" name="settings[timezone]" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                        @foreach (DateTimeZone::listIdentifiers() as $zone)
                            <option value="{{ $zone }}" @selected(old('settings.timezone', $settings['timezone'] ?? config('app.timezone')) === $zone)>{{ $zone }}</option>
                        @endforeach
                    </select>
                    @error('settings.timezone')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="fiscal_year_start_month" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Fiscal year starts in') }}</label>
                    <select id="fiscal_year_start_month" name="settings[fiscal_year_start_month]" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                        @foreach (range(1, 12) as $month)
                            <option value="{{ $month }}" @selected((int) old('settings.fiscal_year_start_month', $settings['fiscal_year_start_month'] ?? 1) === $month)>{{ \Illuminate\Support\Carbon::create()->month($month)->monthName }}</option>
                        @endforeach
                    </select>
                    @error('settings.fiscal_year_start_month')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="invoice_terms" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Default invoice terms') }}</label>
                <textarea id="invoice_terms" name="settings[invoice_terms]" rows="3" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">{{ old('settings.invoice_terms', $settings['invoice_terms'] ?? '') }}</textarea>
                @error('settings.invoice_terms')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
            </div>
            <x-button type="submit">{{ __('Save settings') }}</x-button>
        </form>
    </x-card>
</x-layout.app>
