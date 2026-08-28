{{-- One weather metric --}}
<div class="flex items-center gap-3">
    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-sky-500/15 text-sky-600 dark:text-sky-300">
        <i class="bi {{ $icon }}"></i>
    </span>

    <div class="min-w-0">
        <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $label }}</dt>
        <dd class="truncate font-bold">{{ $value }}</dd>
    </div>
</div>
