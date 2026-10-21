{{-- Shell shared by every dashboard widget card: accent icon, title, open link, body --}}
@props([
    'tile',
    'delay' => 0,
    'action' => null,
])

<section class="glass flex flex-col p-5 animate-fade-up" style="animation-delay: {{ $delay }}ms">
    <header class="mb-3.5 flex items-center gap-3">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-lg text-white shadow-md {{ $tile['gradient'] }}">
            <i class="bi {{ $tile['icon'] }}"></i>
        </span>

        <h2 class="heading min-w-0 flex-1 truncate text-base">{{ $tile['label'] }}</h2>

        <a href="{{ $tile['route'] }}"
           class="flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold text-violet-600 transition-colors
               hover:bg-violet-500/10 hover:text-fuchsia-600
               dark:text-fuchsia-300 dark:hover:bg-fuchsia-500/15 dark:hover:text-fuchsia-200"
           aria-label="Open {{ $tile['label'] }}">
            Open
            <i class="bi bi-arrow-right text-[10px]"></i>
        </a>
    </header>

    <div class="flex flex-1 flex-col justify-center gap-2">
        {{ $slot }}
    </div>

    @if ($action)
        <a href="{{ $action['route'] }}"
           class="mt-3 flex items-center justify-center gap-1.5 rounded-xl border border-dashed border-slate-900/15 py-2
               text-xs font-bold text-slate-500 transition-colors hover:border-violet-500/50 hover:bg-violet-500/10 hover:text-violet-700
               dark:border-white/15 dark:text-slate-400 dark:hover:border-fuchsia-400/40 dark:hover:bg-fuchsia-500/10 dark:hover:text-fuchsia-200">
            <i class="bi bi-plus-lg text-[10px]"></i>
            {{ $action['label'] }}
        </a>
    @endif
</section>
