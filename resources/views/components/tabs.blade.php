@props(['tabs' => []])

<div x-data="{ active: @js(array_key_first($tabs)) }">
    <div class="mb-4 flex gap-1 border-b border-slate-200 dark:border-navy-800" role="tablist">
        @foreach ($tabs as $key => $label)
            <button type="button" role="tab" @click="active = '{{ $key }}'" :aria-selected="active === '{{ $key }}'"
                    class="border-b-2 px-4 py-2 text-sm transition"
                    :class="active === '{{ $key }}' ? 'border-gold-500 text-navy-900 dark:text-white' : 'border-transparent text-slate-500 hover:text-navy-900'">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @foreach ($tabs as $key => $label)
        <div role="tabpanel" x-show="active === '{{ $key }}'" x-cloak>
            @if ($$key ?? null)
                {{ ${$key} }}
            @endif
        </div>
    @endforeach
</div>
