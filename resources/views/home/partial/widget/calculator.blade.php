{{-- Calculator widget: the last few results, read back from the tool's own history --}}
@php($data = $tile['data'])

<x-widget :tile="$tile" :delay="$delay">
    <div class="flex flex-col gap-2" data-widget-calc hidden></div>

    <p class="widget-empty" data-widget-calc-empty>{{ $data['empty'] }}</p>
</x-widget>
