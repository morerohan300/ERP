@props(['hover' => true])

<div {{ $attributes->merge(['class' => 'w-full overflow-x-auto rounded-panel border border-slate-200 bg-white shadow-card dark:border-navy-800 dark:bg-navy-900']) }}>
    <table class="w-full min-w-max text-start text-sm">
        @isset($head)
            <thead class="bg-slate-50 text-xs tracking-wide text-slate-500 uppercase dark:bg-navy-950/40">
                {{ $head }}
            </thead>
        @endisset
        <tbody class="divide-y divide-slate-100 dark:divide-navy-800">
            {{ $slot }}
        </tbody>
    </table>
    @isset($footer)
        <div class="border-t border-slate-100 px-4 py-3 dark:border-navy-800">{{ $footer }}</div>
    @endisset
</div>
