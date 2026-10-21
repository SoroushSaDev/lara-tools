{{-- Music widget: the most recently imported tracks --}}
@php($data = $tile['data'])
@php($isEmpty = $data['items'] === [])

<x-widget :tile="$tile" :delay="$delay" :action="$isEmpty ? $data['action'] : null">
    @if ($isEmpty)
        <p class="widget-empty">{{ $data['empty'] }}</p>
    @else
        @foreach ($data['items'] as $item)
            <x-widget-row :route="$tile['route']" :title="$item['title']" :meta="$item['meta']">
                <x-slot:leading>
                    @if ($item['cover'])
                        <img src="{{ $item['cover'] }}" alt="" loading="lazy"
                             class="size-9 shrink-0 rounded-lg object-cover">
                    @else
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-pink-400 to-fuchsia-600 text-white">
                            <i class="bi bi-music-note-beamed text-sm"></i>
                        </span>
                    @endif
                </x-slot:leading>
            </x-widget-row>
        @endforeach
    @endif
</x-widget>
