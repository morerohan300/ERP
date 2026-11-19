@if (session('status') || session('success') || session('error') || $errors->any())
    @php
        $message = session('status') ?: session('success');
        $type = session('error') || $errors->any() ? 'danger' : 'success';
        $message = $message ?: ($errors->first() ?? session('error'));
    @endphp
    <div class="fixed bottom-4 end-4 z-50 max-w-sm"
         x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
         x-transition.opacity>
        <div class="flex items-start gap-3 rounded-panel border px-4 py-3 shadow-card {{ $type === 'danger' ? 'border-danger/30 bg-white text-danger dark:bg-navy-900' : 'border-success/30 bg-white text-success dark:bg-navy-900' }}">
            <span aria-hidden="true">{{ $type === 'danger' ? '⚠' : '✓' }}</span>
            <p class="flex-1 text-sm">{{ $message }}</p>
            <button type="button" class="text-slate-400 hover:text-slate-600" @click="show = false" aria-label="{{ __('Dismiss') }}">✕</button>
        </div>
    </div>
@endif
