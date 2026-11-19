@props(['items' => []])

@if ($items !== [])
    <nav class="mb-4 flex flex-wrap items-center gap-1 text-xs text-slate-500" aria-label="{{ __('Breadcrumbs') }}">
        <a href="{{ route('dashboard') }}" class="hover:text-gold-600">{{ __('Home') }}</a>
        @foreach ($items as $item)
            <span aria-hidden="true">/</span>
            @if (! empty($item['url']))
                <a href="{{ $item['url'] }}" class="hover:text-gold-600">{{ $item['label'] }}</a>
            @else
                <span class="text-slate-700 dark:text-slate-300">{{ $item['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif
