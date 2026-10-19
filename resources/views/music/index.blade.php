@extends('layouts.main')

@section('title', 'Music Player')

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Music Player</x-slot:title>
        <x-slot:subtitle>{{ $musicFiles->count() }} {{ str('track')->plural($musicFiles->count()) }} · playback keeps running while you browse</x-slot:subtitle>

        <x-slot:actions>
            <a href="{{ route('settings', ['tab' => 'music']) }}" class="icon-btn" title="Music settings">
                <i class="bi bi-gear"></i>
            </a>

            <form method="POST" action="{{ route('music.pick') }}">
                @csrf
                <button type="submit" class="btn-primary">
                    <i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Add music</span>
                </button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-3 pb-24 xl:gap-4">
        @forelse ($musicFiles as $key => $music)
            @include('music.partial.track', ['track' => $music, 'index' => $key])
        @empty
            <x-empty-state icon="bi-music-note-list" title="Your library is empty">
                <x-slot:description>Import audio files or whole folders from your disk.</x-slot:description>

                <x-slot:action>
                    <form method="POST" action="{{ route('music.pick') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn-primary">
                            <i class="bi bi-plus-lg"></i>Add music
                        </button>
                    </form>
                </x-slot:action>
            </x-empty-state>
        @endforelse
    </div>

    {{-- Spacer so the persistent full deck never covers the last row --}}
    <div class="h-28 lg:h-32"></div>
@endsection
