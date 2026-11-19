@props(['title', 'description' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-12 text-center']) }}>
    @if ($icon)<div class="mb-3 grid size-12 place-items-center rounded-full bg-slate-100 text-2xl text-slate-400 dark:bg-navy-800">{{ $icon }}</div>@endif
    <p class="font-heading text-base text-navy-900 dark:text-white">{{ $title }}</p>
    @if ($description)<p class="mt-1 max-w-sm text-sm text-slate-500">{{ $description }}</p>@endif
    @if (isset($action))<div class="mt-4">{{ $action }}</div>@endif
</div>
