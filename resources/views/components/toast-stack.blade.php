{{-- Floating toast notifications fed by flash messages --}}
<div class="pointer-events-none fixed top-4 right-4 z-50 flex w-[min(92vw,22rem)] flex-col gap-2.5">
    @foreach (['success' => 'bi-check-circle-fill', 'error' => 'bi-x-circle-fill', 'info' => 'bi-info-circle-fill'] as $key => $icon)
        @if ($message = session($key))
            <div data-toast
                 class="glass pointer-events-auto flex items-center gap-3 border-l-4 p-4 text-sm animate-toast-in
                    {{ match ($key) {
                        'success' => 'border-l-emerald-500',
                        'error' => 'border-l-rose-500',
                        default => 'border-l-sky-500',
                    } }}">
                <i class="bi {{ $icon }} shrink-0 text-base {{ match ($key) {
                    'success' => 'text-emerald-500',
                    'error' => 'text-rose-500',
                    default => 'text-sky-500',
                } }}"></i>
                <p class="flex-1 leading-snug">{{ $message }}</p>
                <button type="button" class="-m-1 cursor-pointer p-1 text-slate-400 transition hover:text-slate-600 dark:hover:text-slate-200"
                        data-toast-dismiss title="Dismiss">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>
        @endif
    @endforeach
</div>
