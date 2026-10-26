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
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                <x-search-box :action="route('music.index')" frame="music-results" :value="$query"
                              :params="$likedOnly ? ['liked' => 1] : []"
                              class="max-w-md" placeholder="Search titles, artists, albums and genres…" label="Search music"/>

                {{-- Full-page visits, like the search box's Clear link: the rows
                     live in a frame that targets _top. --}}
                <nav class="flex items-center gap-1.5" aria-label="Library filters">
                    <a href="{{ route('music.index', ['q' => $query]) }}" aria-current="{{ $likedOnly ? 'false' : 'page' }}"
                       @class(['tab-pill', 'is-active' => ! $likedOnly])>
                        All

                        <span class="text-xs font-bold tabular-nums opacity-70">{{ $total }}</span>
                    </a>

                    <a href="{{ route('music.index', ['q' => $query, 'liked' => 1]) }}" aria-current="{{ $likedOnly ? 'true' : 'false' }}"
                       @class(['tab-pill', 'is-active' => $likedOnly])>
                        <i class="bi {{ $likedOnly ? 'bi-heart-fill' : 'bi-heart' }}"></i>Liked

                        {{-- modules/music-likes.js keeps this number in step
                             with the hearts, since the header never reloads. --}}
                        <span class="text-xs font-bold tabular-nums opacity-70" data-liked-count>{{ $likedTotal }}</span>
                    </a>
                </nav>
            </div>
        </x-slot:toolbar>
    </x-page-header>

    @include('music.partial.results')

    {{-- Spacer so the persistent full deck never covers the last row --}}
    <div class="h-28 lg:h-32"></div>
@endsection
