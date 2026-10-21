{{-- Clock widget: server-rendered time, then kept ticking by dashboard-widgets.js --}}
@php($data = $tile['data'])

<x-widget :tile="$tile" :delay="$delay">
    <div class="text-center">
        <p class="heading font-display text-4xl leading-none tabular-nums"
           data-widget-clock
           data-format="{{ $data['format'] }}"
           data-seconds="{{ $data['seconds'] ? '1' : '0' }}">{{ $data['time'] }}</p>

        <p class="mt-1.5 text-xs font-bold text-slate-500 dark:text-slate-400" data-widget-date>{{ $data['date'] }}</p>

        <p class="mt-3 text-[11px] font-bold text-slate-400 dark:text-slate-500" data-widget-alarms hidden></p>
    </div>
</x-widget>
