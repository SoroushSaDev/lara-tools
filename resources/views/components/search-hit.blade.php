@props([
    'href',
    'icon',
    'title',
    'meta' => null,
])

{{-- One global-search result row --}}
<a href="{{ $href }}"
   class="flex items-center gap-3 rounded-xl px-2.5 py-2 transition-colors hover:bg-slate-900/5 dark:hover:bg-white/[0.06]">
    <i class="bi {{ $icon }} shrink-0 text-base text-violet-500 dark:text-fuchsia-300"></i>

    <span class="min-w-0 flex-1">
        <span class="block truncate text-sm font-bold">{{ $title }}</span>

        @if ($meta)
            <span class="block truncate text-xs font-semibold text-slate-400 dark:text-slate-500">{{ $meta }}</span>
        @endif
    </span>

    <i class="bi bi-arrow-right shrink-0 text-xs text-slate-300 dark:text-slate-600"></i>
</a>
