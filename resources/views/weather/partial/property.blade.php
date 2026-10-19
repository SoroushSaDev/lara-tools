{{-- One weather metric. Props: $icon, $label, $value, optional $hint, optional $tone --}}
@php
    $tone ??= 'sky';

    $toneClasses = [
        'sky' => 'bg-sky-500/15 text-sky-600 dark:text-sky-300',
        'emerald' => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300',
        'amber' => 'bg-amber-500/15 text-amber-600 dark:text-amber-300',
        'violet' => 'bg-violet-500/15 text-violet-600 dark:text-violet-300',
        'rose' => 'bg-rose-500/15 text-rose-600 dark:text-rose-300',
        'slate' => 'bg-slate-500/15 text-slate-600 dark:text-slate-300',
    ];
@endphp

<div class="flex items-center gap-3">
    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $toneClasses[$tone] ?? $toneClasses['sky'] }}">
        <i class="bi {{ $icon }}"></i>
    </span>

    <div class="min-w-0">
        <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $label }}</dt>
        <dd class="truncate font-bold">{{ $value }}</dd>

        @if (filled($hint ?? null))
            <p class="truncate text-[11px] font-semibold text-slate-400 dark:text-slate-500">{{ $hint }}</p>
        @endif
    </div>
</div>
