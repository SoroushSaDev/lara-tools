{{-- Todo widget: the newest lists and how far each one has got --}}
@php($data = $tile['data'])
@php($isEmpty = $data['items'] === [])

<x-widget :tile="$tile" :delay="$delay" :action="$isEmpty ? $data['action'] : null">
    @if ($isEmpty)
        <p class="widget-empty">{{ $data['empty'] }}</p>
    @else
        @foreach ($data['items'] as $item)
            <x-widget-row :route="$item['route']" :title="$item['title']" :meta="$item['percent'].'%'">
                <x-slot:leading>
                    <i class="bi mt-0.5 shrink-0 text-base {{ $item['overdue']
                        ? 'bi-exclamation-diamond-fill text-rose-500'
                        : 'bi-check-circle-fill text-emerald-500 dark:text-emerald-300' }}"></i>
                </x-slot:leading>

                <x-slot:sub>
                    <span class="progress-track mt-2 block">
                        <span class="progress-fill block" style="width: {{ $item['percent'] }}%"></span>
                    </span>

                    <span class="mt-1 block text-[11px] font-bold {{ $item['overdue']
                        ? 'text-rose-500 dark:text-rose-300'
                        : 'text-slate-400 dark:text-slate-500' }}">
                        {{ $item['overdue'] ? 'Overdue · ' : '' }}{{ $item['meta'] }}
                    </span>
                </x-slot:sub>
            </x-widget-row>
        @endforeach
    @endif
</x-widget>
