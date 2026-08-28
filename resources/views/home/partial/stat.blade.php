{{-- Compact statistic pill used on the dashboard --}}
<div class="glass flex items-center gap-3.5 p-4">
    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-lg text-white shadow-md {{ $gradient }}">
        <i class="bi {{ $icon }}"></i>
    </span>

    <div class="min-w-0">
        <p class="heading truncate text-xl leading-tight">{{ $value }}</p>
        <p class="truncate text-xs font-bold text-slate-500 dark:text-slate-400">{{ $label }}</p>
    </div>
</div>
