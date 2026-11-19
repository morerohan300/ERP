<x-layout.app :title="__('Company profile')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Company profile')]]">
    <x-card :title="__('Company profile')" :subtitle="__('Details used across documents and reports.')" class="max-w-4xl">
        <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="flex items-center gap-4">
                @if ($company->logoUrl())
                    <img src="{{ $company->logoUrl() }}" alt="" class="size-16 rounded-panel object-contain ring-1 ring-slate-200 dark:ring-navy-800">
                @endif
                <x-form.input name="logo" :label="__('Logo')" type="file" accept="image/*" />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <x-form.input name="name" :label="__('Company name')" :value="$company->name" required />
                <x-form.input name="legal_name" :label="__('Legal name')" :value="$company->legal_name" />
                <x-form.input name="tax_number" :label="__('Tax number')" :value="$company->tax_number" />
                <x-form.input name="registration_number" :label="__('Registration number')" :value="$company->registration_number" />
                <x-form.input name="email" :label="__('Email')" type="email" :value="$company->email" />
                <x-form.input name="phone" :label="__('Phone')" :value="$company->phone" />
                <x-form.input name="website" :label="__('Website')" :value="$company->website" />
                <x-form.input name="industry" :label="__('Industry')" :value="$company->industry" />

                <div>
                    <label for="base_currency_code" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Base currency') }}</label>
                    <select id="base_currency_code" name="base_currency_code" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                        @foreach ($currencies as $code => $name)
                            <option value="{{ $code }}" @selected(old('base_currency_code', $company->base_currency_code) === $code)>{{ $code }} — {{ $name }}</option>
                        @endforeach
                    </select>
                    @error('base_currency_code')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="locale" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Language') }}</label>
                    <select id="locale" name="locale" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                        @foreach ($locales as $code => $language)
                            <option value="{{ $code }}" @selected(old('locale', $company->locale) === $code)>{{ $language['native'] }}</option>
                        @endforeach
                    </select>
                    @error('locale')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="timezone" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Timezone') }}</label>
                    <select id="timezone" name="timezone" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                        @foreach (DateTimeZone::listIdentifiers() as $zone)
                            <option value="{{ $zone }}" @selected(old('timezone', $company->timezone) === $zone)>{{ $zone }}</option>
                        @endforeach
                    </select>
                    @error('timezone')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="fiscal_year_start_month" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Fiscal year starts in') }}</label>
                    <select id="fiscal_year_start_month" name="fiscal_year_start_month" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                        @foreach (range(1, 12) as $month)
                            <option value="{{ $month }}" @selected((int) old('fiscal_year_start_month', $company->fiscal_year_start_month) === $month)>{{ \Illuminate\Support\Carbon::create()->month($month)->monthName }}</option>
                        @endforeach
                    </select>
                    @error('fiscal_year_start_month')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="date_format" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">{{ __('Date format') }}</label>
                    <select id="date_format" name="date_format" class="w-full rounded-control border-slate-300 bg-white text-sm dark:border-navy-700 dark:bg-navy-950 dark:text-white">
                        @foreach ($dateFormats as $format => $example)
                            <option value="{{ $format }}" @selected(old('date_format', $company->date_format) === $format)>{{ $example }}</option>
                        @endforeach
                    </select>
                    @error('date_format')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            <x-form.checkbox name="is_active" :label="__('Active')" :checked="$company->is_active" />
            <x-button type="submit">{{ __('Save company profile') }}</x-button>
        </form>
    </x-card>
</x-layout.app>
