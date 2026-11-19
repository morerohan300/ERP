@props(['label' => null, 'name', 'options' => [], 'value' => null, 'placeholder' => null, 'required' => false])

<div x-data="{
        open: false,
        query: '',
        selected: @js(old($name, $value)),
        options: @js($options),
        get filtered() {
            return Object.entries(this.options).filter(([, label]) => String(label).toLowerCase().includes(this.query.toLowerCase()));
        }
     }" x-on:click.outside="open = false" class="relative">
    @if ($label)
        <label for="{{ $name }}-search" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">
            {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
        </label>
    @endif
    <input type="hidden" name="{{ $name }}" :value="selected">
    <input id="{{ $name }}-search" type="text" x-model="query" placeholder="{{ $placeholder }}"
           @focus="open = true" @input="open = true"
           :value="options[selected] ?? ''"
           autocomplete="off"
           class="w-full rounded-control border-slate-300 bg-white px-3 py-2 text-sm focus:border-gold-500 focus:ring-gold-500 dark:border-navy-700 dark:bg-navy-950 dark:text-white">
    <ul x-show="open && filtered.length" x-cloak class="absolute z-30 mt-1 max-h-56 w-full overflow-y-auto rounded-panel border border-slate-200 bg-white py-1 shadow-card dark:border-navy-800 dark:bg-navy-900">
        <template x-for="([key, label]) in filtered" :key="key">
            <li>
                <button type="button" class="block w-full px-3 py-2 text-start text-sm hover:bg-slate-50 dark:hover:bg-navy-800"
                        @click="selected = key; query = label; open = false" x-text="label"></button>
            </li>
        </template>
    </ul>
    @error($name)<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
</div>
