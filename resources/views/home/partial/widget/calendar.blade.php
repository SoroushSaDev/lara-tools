{{-- Calendar widget: the next three events, with the sooner ones accented --}}
@php($data = $tile['data'])
@php($isEmpty = $data['items'] === [])

<x-widget :tile="$tile" :delay="$delay" :action="$isEmpty ? $data['action'] : null">
    @if ($isEmpty)
        <p class="widget-empty">{{ $data['empty'] }}</p>
    @else
        @foreach ($data['items'] as $item)
            <x-widget-row :route="$item['route']" :title="$item['title']">
                <x-slot:leading>
                    <i class="bi bi-calendar-event mt-0.5 shrink-0 text-base {{ $item['soon']
                        ? 'text-violet-600 dark:text-fuchsia-300'
                        : 'text-slate-400 dark:text-slate-500' }}"></i>
                </x-slot:leading>

                <x-slot:sub>
                    <span class="mt-0.5 block text-xs font-bold {{ $item['soon']
                        ? 'text-violet-600 dark:text-fuchsia-300'
                        : 'text-slate-500 dark:text-slate-400' }}">
                        {{ $item['meta'] }}
                    </span>
                </x-slot:sub>
            </x-widget-row>
        @endforeach
    @endif
</x-widget>
