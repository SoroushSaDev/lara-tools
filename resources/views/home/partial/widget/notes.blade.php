{{-- Notes widget: the newest notes with a short plain-text excerpt --}}
@php($data = $tile['data'])
@php($isEmpty = $data['items'] === [])

<x-widget :tile="$tile" :delay="$delay" :action="$isEmpty ? $data['action'] : null">
    @if ($isEmpty)
        <p class="widget-empty">{{ $data['empty'] }}</p>
    @else
        @foreach ($data['items'] as $item)
            <x-widget-row :route="$item['route']" :title="$item['title']" :meta="$item['meta']">
                <x-slot:leading>
                    <i class="bi bi-journal-text mt-0.5 shrink-0 text-base text-amber-500 dark:text-amber-300"></i>
                </x-slot:leading>

                @if ($item['text'] !== '')
                    <x-slot:sub>
                        <span class="mt-0.5 line-clamp-2 text-xs font-semibold leading-snug text-slate-500 dark:text-slate-400">
                            {{ $item['text'] }}
                        </span>
                    </x-slot:sub>
                @endif
            </x-widget-row>
        @endforeach
    @endif
</x-widget>
