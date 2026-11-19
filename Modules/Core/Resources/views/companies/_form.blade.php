<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="name" :label="__('Company name')" :value="old('name')" required autofocus />
    <x-form.input name="legal_name" :label="__('Legal name')" :value="old('legal_name')" />
    <x-form.input name="tax_number" :label="__('Tax number')" :value="old('tax_number')" />
    <x-form.input name="registration_number" :label="__('Registration number')" :value="old('registration_number')" />
    <x-form.input name="email" :label="__('Email')" type="email" :value="old('email')" />
    <x-form.input name="phone" :label="__('Phone')" :value="old('phone')" />
    <x-form.input name="website" :label="__('Website')" :value="old('website')" />
    <x-form.input name="industry" :label="__('Industry')" :value="old('industry')" />
    <x-form.input name="address[line1]" :label="__('Address')" :value="old('address.line1')" />
    <x-form.input name="address[city]" :label="__('City')" :value="old('address.city')" />
    <x-form.select name="base_currency_code" :label="__('Base currency')" :options="$currencies" :value="old('base_currency_code', config('tenancy.default_currency'))" />
    <x-form.select name="locale" :label="__('Language')" :options="collect(config('locales.supported'))->map(fn ($l) => $l['native'])" :value="old('locale', 'en')" />
    <div>
        <label for="timezone" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Timezone') }}</label>
        <select id="timezone" name="timezone" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
            @foreach (DateTimeZone::listIdentifiers() as $zone)
                <option value="{{ $zone }}" @selected(old('timezone', config('app.timezone')) === $zone)>{{ $zone }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="fiscal_year_start_month" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Fiscal year starts in') }}</label>
        <select id="fiscal_year_start_month" name="fiscal_year_start_month" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
            @foreach (range(1, 12) as $month)
                <option value="{{ $month }}" @selected((int) old('fiscal_year_start_month', 1) === $month)>{{ \Illuminate\Support\Carbon::create()->month($month)->monthName }}</option>
            @endforeach
        </select>
    </div>
    <x-form.select name="date_format" :label="__('Date format')" :options="collect(config('locales.date_formats'))->map(fn ($example, $format) => $example)" :value="old('date_format', 'd M Y')" />
    <x-form.input name="logo" :label="__('Logo')" type="file" accept="image/*" />
</div>
