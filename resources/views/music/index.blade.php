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
    <div class="grid max-sm:grid-cols-1 sm:grid-cols-3 gap-2 w-full">
        @forelse($musicFiles as $key => $music)
            @include('music.partial.music', ['music' => $music, 'key' => $key])
        @empty
            <p class="font-semibold text-center">
                You don't have any music yet
            </p>
        @endforelse
    </div>
@endsection
@section('bottom-bar')
    <div id="player"
         class="hidden flex-col space-y-3 sm:space-y-5 backdrop-blur-3xl bg-white/30 dark:bg-black/30 shadow-2xl p-2 sm:p-6 sm:text-xl">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <img id="player-cover" src="" alt="Cover"
                     class="max-sm:w-10 max-sm:h-10 sm:w-20 sm:h-20 rounded object-cover hidden">
                <div>
                    <h3 id="player-title" class="font-semibold sm:text-2xl"></h3>
                    <p id="player-artist" class="max-sm:text-xs"></p>
                </div>
                <audio id="audio-player" controls class="w-full mt-1 hidden">
                    <source id="audio-source" src="">
                    Your browser does not support the audio element.
                </audio>
            </div>
            <div>
                <button type="button" id="next"
                        class="hover:cursor-pointer hover:backdrop-blur-3xl px-2 py-1 rounded-full"
                        onclick="playPrevious();">
                    <i class="bi bi-skip-start-fill"></i>
                </button>
                <button type="button" id="toggle"
                        class="hover:cursor-pointer backdrop-blur-3xl hover:text-black hover:bg-white px-2 py-1 rounded-full"
                        data-status="paused" onclick="toggleAudio();">
                    <i id="toggle-icon" class="bi bi-play-fill"></i>
                </button>
                <button type="button" id="next"
                        class="hover:cursor-pointer hover:backdrop-blur-3xl px-2 py-1 rounded-full"
                        onclick="playNext();">
                    <i class="bi bi-skip-end-fill"></i>
                </button>
            </div>
        </div>
        <div class="flex items-center space-x-2 sm:font-bold">
            <span id="current-time" class="text-sm">0:00</span>
            <input type="range" id="progress-bar" class="w-full" min="0" max="100" value="0">
            <span id="total-duration" class="text-sm">0:00</span>
        </div>
        <!-- <div class="flex items-center gap-2">
            <label for="volume-slider" class="text-sm">🔊</label>
            <input type="range" id="volume-slider" min="0" max="1" step="0.01" value="1">
        </div> -->
    </div>
@endsection
@push('script')
    <script>
        let current = null;

        const player = document.getElementById('player');
        const toggler = document.getElementById('toggle');
        const audio = document.getElementById('audio-player');
        const source = document.getElementById('audio-source');
        const togglerIcon = document.getElementById('toggle-icon');

        // Format seconds into mm:ss
        function formatTime(sec) {
            const mins = Math.floor(sec / 60);
            const secs = Math.floor(sec % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        function playAudio() {
            audio.play();
            toggler.dataset.status = 'playing';
            togglerIcon.classList.remove('bi-play-fill');
            togglerIcon.classList.add('bi-pause');
        }

        function playMusic(key) {
            const item = document.getElementById('music-' + key);

            const path = item.dataset.path;
            const title = item.dataset.title;
            const artist = item.dataset.artist;
            const cover = item.dataset.cover;

            const titleEl = document.getElementById('player-title');
            const artistEl = document.getElementById('player-artist');
            const coverEl = document.getElementById('player-cover');
            const progressBar = document.getElementById('progress-bar');
            const currentTimeEl = document.getElementById('current-time');
            const totalDurationEl = document.getElementById('total-duration');
            const volumeSlider = document.getElementById('volume-slider');

            source.src = path;
            audio.load();
            playAudio();

            current = key;

            titleEl.textContent = title;
            artistEl.textContent = artist ?? '';
            if (cover) {
                coverEl.src = cover;
                coverEl.classList.remove('hidden');
            } else {
                coverEl.classList.add('hidden');
            }

            player.classList.remove('hidden');
            player.classList.add('flex');

            // Update progress bar and time display
            audio.addEventListener('loadedmetadata', () => {
                totalDurationEl.textContent = formatTime(audio.duration);
            });

            audio.addEventListener('timeupdate', () => {
                if (!isNaN(audio.duration)) {
                    progressBar.value = (audio.currentTime / audio.duration) * 100;
                    currentTimeEl.textContent = formatTime(audio.currentTime);
                }
            });

            // Seek audio
            progressBar.addEventListener('input', () => {
                if (!isNaN(audio.duration)) {
                    audio.currentTime = (progressBar.value / 100) * audio.duration;
                }
            });

            // Volume control
            volumeSlider.addEventListener('input', () => {
                audio.volume = volumeSlider.value;
            });
        }

        function toggleAudio() {
            if (toggler.dataset.status == 'paused') {
                playAudio();
            } else {
                audio.pause();
                toggler.dataset.status = 'paused';
                togglerIcon.classList.remove('bi-pause');
                togglerIcon.classList.add('bi-play-fill');
            }
        }

        function playNext() {
            newKey = parseInt(current) + 1;
            playMusic(newKey);
        }

        function playPrevious() {
            newKey = parseInt(current) - 1;
            playMusic(newKey);
        }
    </script>
@endpush
