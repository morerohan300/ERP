<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" class="relative rounded-control p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-navy-800" @click="open = !open" aria-label="{{ __('Notifications') }}">
        <span aria-hidden="true">♧</span>
        @if ($unread > 0)
            <span class="absolute -top-1 -end-1 grid size-4 place-items-center rounded-full bg-danger text-[10px] font-semibold text-white">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </button>

    <div class="absolute end-0 z-50 mt-1 w-80 max-w-[calc(100vw-2rem)] rounded-panel border border-slate-200 bg-white shadow-card dark:border-navy-800 dark:bg-navy-900"
         x-show="open" x-cloak x-transition.opacity>
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-navy-800">
            <p class="font-heading text-sm text-navy-900 dark:text-white">{{ __('Notifications') }}</p>
            @if ($unread > 0)
                <button type="button" wire:click="markAllRead" class="text-xs text-gold-600 hover:underline">{{ __('Mark all read') }}</button>
            @endif
        </div>

        <div class="max-h-96 divide-y divide-slate-100 overflow-y-auto dark:divide-navy-800">
            @forelse ($notifications as $notification)
                <div class="flex gap-3 px-4 py-3 {{ $notification['read'] ? '' : 'bg-gold-500/5' }}">
                    <span class="mt-0.5 text-gold-600" aria-hidden="true">◎</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-navy-900 dark:text-white">{{ $notification['title'] }}</p>
                        @if ($notification['body'])<p class="mt-0.5 text-xs text-slate-500">{{ $notification['body'] }}</p>@endif
                        <div class="mt-1 flex items-center gap-3">
                            @if ($notification['url'])
                                <a href="{{ $notification['url'] }}" wire:click="markRead('{{ $notification['id'] }}')" class="text-xs text-gold-600 hover:underline">{{ __('View') }}</a>
                            @endif
                            @if (! $notification['read'])
                                <button type="button" wire:click="markRead('{{ $notification['id'] }}')" class="text-xs text-slate-500 hover:underline">{{ __('Mark read') }}</button>
                            @endif
                            <span class="text-xs text-slate-400">{{ $notification['created_at']?->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-slate-500">{{ __('You are all caught up.') }}</div>
            @endforelse
        </div>
    </div>
</div>
