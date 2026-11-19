<x-layout.app :title="__('Modules')" :breadcrumbs="[['label' => __('Settings')], ['label' => __('Modules')]]">
    <x-card :title="__('Module access')" :subtitle="__('Enable or disable features for the active company.')">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($modules as $module)
                @php $enabled = $manager->enabled($module->key, $companyId); @endphp
                <div class="flex flex-col rounded-panel border border-slate-200 p-4 dark:border-navy-800">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-heading text-sm text-navy-900 dark:text-white">{{ $module->label }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $module->description }}</p>
                        </div>
                        <x-badge :color="$enabled ? 'success' : 'slate'" :dot="true">{{ $enabled ? __('Enabled') : __('Disabled') }}</x-badge>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs text-slate-400">
                        <span>v{{ $module->version }}</span>
                        @if ($module->core)<x-badge color="gold">{{ __('Core') }}</x-badge>@endif
                    </div>
                    @if ($module->dependencies)
                        <p class="mt-2 text-xs text-slate-400">{{ __('Requires: :modules', ['modules' => implode(', ', $module->dependencies)]) }}</p>
                    @endif
                    <div class="mt-auto pt-4">
                        @if ($module->core)
                            <span class="text-xs text-slate-400">{{ __('Always enabled') }}</span>
                        @else
                            <form method="POST" action="{{ route('modules.toggle', $module->key) }}">
                                @csrf
                                <input type="hidden" name="enabled" value="{{ $enabled ? '0' : '1' }}">
                                <x-button type="submit" :variant="$enabled ? 'secondary' : 'accent'" size="sm">
                                    {{ $enabled ? __('Disable') : __('Enable') }}
                                </x-button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
</x-layout.app>
