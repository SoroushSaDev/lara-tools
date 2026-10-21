{{-- Weather widget: the home town's last stored reading, never a live fetch --}}
@php($data = $tile['data'])
@php($isEmpty = isset($data['empty']))

<x-widget :tile="$tile" :delay="$delay" :action="$isEmpty ? $data['action'] : null">
    @if ($isEmpty)
        <p class="widget-empty">{{ $data['empty'] }}</p>
    @else
        @php($place = $data['city'].($data['country'] ? ', '.$data['country'] : ''))

        <div class="flex items-center gap-3.5">
            <i class="bi {{ $data['icon'] }} shrink-0 text-4xl text-sky-500 dark:text-sky-300"></i>

            <div class="min-w-0">
                <p class="heading truncate text-3xl leading-none tabular-nums">{{ $data['temp'] ?? '—' }}</p>
                <p class="mt-1 truncate text-xs font-bold text-slate-500 dark:text-slate-400">{{ $data['condition'] }}</p>
            </div>

            <div class="ml-auto min-w-0 text-right">
                <p class="truncate text-sm font-bold" title="{{ $place }}">{{ $place }}</p>
                <p class="mt-0.5 text-[11px] font-bold text-slate-400 dark:text-slate-500">{{ $data['updated'] }}</p>
            </div>
        </div>

        <div class="mt-3 grid grid-cols-3 gap-2">
            <div class="widget-stat">
                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Feels</p>
                <p class="mt-0.5 text-sm font-bold tabular-nums">{{ $data['feels'] ?? '—' }}</p>
            </div>

            <div class="widget-stat">
                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Humidity</p>
                <p class="mt-0.5 text-sm font-bold tabular-nums">{{ $data['humidity'] ?? '—' }}</p>
            </div>

            <div class="widget-stat">
                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Wind</p>
                <p class="mt-0.5 text-sm font-bold tabular-nums">{{ $data['wind'] ?? '—' }}</p>
            </div>
        </div>
    @endif
</x-widget>
