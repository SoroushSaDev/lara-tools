{{-- Converter widget: quick picks, each opening the converter --}}
@php($data = $tile['data'])

<x-widget :tile="$tile" :delay="$delay">
    <div class="grid grid-cols-2 gap-2">
        @foreach ($data['picks'] as $pick)
            <a href="{{ $tile['route'] }}"
               class="flex items-center gap-2 rounded-xl border border-slate-900/5 bg-white/40 px-3 py-2.5 text-xs font-bold
                   transition-colors hover:bg-white
                   dark:border-white/5 dark:bg-white/[0.04] dark:hover:bg-white/10">
                <i class="bi {{ $pick['icon'] }} shrink-0 text-base text-cyan-600 dark:text-cyan-300"></i>
                <span class="min-w-0 truncate">{{ $pick['label'] }}</span>
            </a>
        @endforeach
    </div>
</x-widget>
