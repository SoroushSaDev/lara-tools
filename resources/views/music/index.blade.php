@extends('layouts.main')

@section('title', 'Music Player')

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Music Player</x-slot:title>
        <x-slot:subtitle>{{ $total }} {{ str('track')->plural($total) }} · playback keeps running while you browse</x-slot:subtitle>

        <x-slot:actions>
            <a href="{{ route('settings', ['tab' => 'music']) }}" class="icon-btn" title="Music settings">
                <i class="bi bi-gear"></i>
            </a>

            <form method="POST" action="{{ route('music.pick') }}" data-turbo-prefetch="false">
                @csrf
                <input type="hidden" name="intent" value="pick"/>

                <button type="submit" class="btn-primary">
                    <i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Add music</span>
                </button>
            </form>
        </x-slot:actions>

        <x-slot:toolbar>
            <x-search-box :action="route('music.index')" frame="music-results" :value="$query"
                          class="max-w-md" placeholder="Search titles, artists, albums and genres…" label="Search music"/>
        </x-slot:toolbar>
    </x-page-header>

    @include('music.partial.results')

    {{-- Spacer so the persistent full deck never covers the last row --}}
    <div class="h-28 lg:h-32"></div>
@endsection
