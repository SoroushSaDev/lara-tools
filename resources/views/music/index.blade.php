@extends('layouts.main')
@section('header-s')
    <a href="{{ route('home') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="invisible lg:visible">
            Home
        </span>
    </a>
@endsection
@section('title', 'Music Player')
@section('header-e')
    <a href="{{ route('music.pick') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-plus-lg"></i>
        <span class="hidden lg:block">
            Add Music
        </span>
    </a>
@endsection
@section('content')
    <div class="flex flex-col space-y-2 w-full">
        @forelse($musicFiles as $music)
            <div
                onclick="playMusic('{{ asset('storage/' . $music->path) }}', '{{ $music->title ?? $music->name }}', '{{ $music->artist }}', '{{ asset('storage/' . $music->cover_image) }}')"
                class="flex justify-between items-center backdrop-blur-3xl bg-white/30 dark:bg-black/30 p-3 rounded-lg hover:shadow-2xl hover:cursor-pointer">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('storage/' . $music->cover_image) }}" alt="Cover Art"
                         class="w-10 h-10 object-cover rounded">
                    <div>
                        <h2 class="font-semibold">
                            {{ $music->title ?? $music->name }}
                        </h2>
                        <p class="text-sm">
                            {{ $music->artist ?? 'Unknown artist' }} {{ $music->album ? (' | ' . $music->album) : '' }}
                        </p>
                    </div>
                </div>
                <div>
                    <button type="button" class="hover:cursor-pointer hover:backdrop-blur-3xl px-2 py-1 rounded-full">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                </div>
                {{--                @if ($music->duration)--}}
                {{--                    <p><strong>Duration:</strong> {{ gmdate('i\:s', $music->duration) }}</p>--}}
                {{--                @endif--}}
                {{--                <audio controls class="mt-2 w-full">--}}
                {{--                    <source src="{{ asset('storage/' . $music->path) }}">--}}
                {{--                    Your browser does not support the audio element.--}}
                {{--                </audio>--}}
            </div>
        @empty
            <p class="font-semibold text-center">
                You don't have any music yet
            </p>
        @endforelse
    </div>
    <div
        class="fixed bottom-16 left-0 w-full p-3 transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
        <div
            class="flex justify-between items-center backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-md shadow-2xl p-2">
            <div class="flex items-center space-x-3">
                <img id="player-cover" src="" alt="Cover" class="w-16 h-16 rounded object-cover hidden">
                <div>
                    <h3 id="player-title" class="font-semibold"></h3>
                    <p id="player-artist" class="text-sm text-gray-600"></p>
                </div>
                <audio id="audio-player" controls class="w-full mt-1 hidden">
                    <source id="audio-source" src="">
                    Your browser does not support the audio element.
                </audio>
            </div>
            <div>
                <button type="button" class="hover:cursor-pointer hover:backdrop-blur-3xl px-2 py-1 rounded-full">
                    <i class="bi bi-play-fill"></i>
                </button>
            </div>
        </div>
    </div>
    @push('script')
        <script>
            const audio = document.getElementById('audio-player');

            function playMusic(path, title, artist, cover) {
                const source = document.getElementById('audio-source');
                const titleEl = document.getElementById('player-title');
                const artistEl = document.getElementById('player-artist');
                const coverEl = document.getElementById('player-cover');

                source.src = path;
                audio.load();
                audio.play();

                titleEl.textContent = title;
                artistEl.textContent = artist ?? '';
                if (cover) {
                    coverEl.src = cover;
                    coverEl.classList.remove('hidden');
                } else {
                    coverEl.classList.add('hidden');
                }
            }
        </script>
    @endpush
@endsection
