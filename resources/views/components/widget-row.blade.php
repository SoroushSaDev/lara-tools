{{-- One linked row inside a dashboard widget: leading glyph/cover, title, sub, right-aligned meta --}}
@props([
    'route',
    'title',
    'meta' => null,
])

<a href="{{ $route }}"
   class="group flex items-start gap-3 rounded-xl border border-slate-900/5 bg-white/40 p-2.5 transition-colors
       hover:bg-white dark:border-white/5 dark:bg-white/[0.04] dark:hover:bg-white/10">
    @isset($leading)
        {{ $leading }}
    @endisset

    <span class="min-w-0 flex-1">
        <span class="flex items-baseline justify-between gap-2">
            <span class="min-w-0 flex-1 truncate text-sm font-bold transition-colors group-hover:text-violet-600 dark:group-hover:text-fuchsia-300">
                {{ $title }}
            </span>

            @if (filled($meta))
                {{-- Capped so a long artist or album name cannot push the row open. --}}
                <span class="max-w-[45%] shrink-0 truncate text-right text-[11px] font-bold text-slate-400 dark:text-slate-500">
                    {{ $meta }}
                </span>
            @endif
        </span>

        @isset($sub)
            {{ $sub }}
        @endisset
    </span>
</a>
