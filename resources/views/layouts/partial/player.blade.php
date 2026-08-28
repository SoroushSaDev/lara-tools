{{-- Persistent music player: compact pill on every page, full deck on the
     Music page (body receives .on-music-page from the layout). Marked
     data-turbo-permanent so playback survives Turbo navigation.
     Duplicated controls (cover, title, play icon, actions) are wired through
     data-* hooks because they appear in both modes. --}}
<div id="player" data-turbo-permanent
     class="fixed bottom-[4.6rem] right-3 z-30 hidden flex-col overflow-hidden rounded-2xl border border-white/70 bg-white/85 shadow-2xl shadow-slate-900/20 backdrop-blur-2xl
         lg:bottom-5 lg:right-6 dark:border-white/10 dark:bg-slate-900/85 animate-fade-up">
    {{-- Thin progress line shared by both modes --}}
    <span class="absolute inset-x-0 top-0 h-0.5 bg-slate-900/10 dark:bg-white/10">
        <span class="block h-full bg-gradient-to-r from-violet-500 to-fuchsia-500 transition-all duration-300" data-player-line style="width: 0%"></span>
    </span>

    {{-- Mini mode (every page except Music) --}}
    <div class="player-mini flex items-center gap-3 p-2.5 pe-3.5">
        <span class="relative flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-pink-400 to-fuchsia-600 text-base text-white shadow-md">
            <img src="" alt="" class="absolute inset-0 size-full object-cover hidden" data-player-cover/>
            <i class="bi bi-vinyl-fill"></i>
        </span>

        <div class="min-w-0 flex-1 leading-tight">
            <h3 class="heading truncate text-xs" data-player-title>Nothing playing</h3>
            <p class="truncate text-[11px] font-bold text-slate-500 dark:text-slate-400" data-player-artist></p>
        </div>

        <a href="{{ route('music.index') }}" class="icon-btn !size-8 shrink-0 !border-0 !bg-transparent" title="Open library">
            <i class="bi bi-music-note-list"></i>
        </a>

        <button type="button" data-player-action="play"
                class="flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-full bg-gradient-to-br from-violet-600 to-fuchsia-600 text-white shadow-md shadow-violet-600/40 transition-transform hover:scale-105 active:scale-95"
                title="Play / pause">
            <i class="bi bi-play-fill translate-x-[1px]" data-player-play-icon></i>
        </button>

        <button type="button" data-player-action="next" class="icon-btn !size-8 shrink-0 !border-0 !bg-transparent" title="Next">
            <i class="bi bi-skip-end-fill"></i>
        </button>
    </div>

    {{-- Full mode (Music page) --}}
    <div class="player-full flex-col gap-3 p-4">
        <div class="flex items-center gap-3">
            <span class="relative flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-pink-400 to-fuchsia-600 text-xl text-white shadow-md sm:size-14">
                <img src="" alt="" class="absolute inset-0 size-full object-cover hidden" data-player-cover/>
                <i class="bi bi-vinyl-fill"></i>
            </span>

            <div class="min-w-0 flex-1">
                <h3 class="heading truncate text-sm sm:text-base" data-player-title>Nothing playing</h3>
                <p class="truncate text-xs font-bold text-slate-500 dark:text-slate-400" data-player-artist></p>
            </div>

            {{-- Transport controls --}}
            <div class="flex items-center gap-1 sm:gap-1.5">
                <button type="button" data-player-action="shuffle" class="icon-btn !size-9 !border-0 !bg-transparent" title="Shuffle">
                    <i class="bi bi-shuffle"></i>
                </button>

                <button type="button" data-player-action="prev" class="icon-btn !size-9 !border-0 !bg-transparent" title="Previous">
                    <i class="bi bi-skip-start-fill text-lg"></i>
                </button>

                <button type="button" data-player-action="play"
                        class="flex size-11 cursor-pointer items-center justify-center rounded-full bg-gradient-to-br from-violet-600 to-fuchsia-600 text-xl text-white shadow-lg shadow-violet-600/40 transition-transform hover:scale-105 active:scale-95"
                        title="Play / pause">
                    <i class="bi bi-play-fill translate-x-[1px]" data-player-play-icon></i>
                </button>

                <button type="button" data-player-action="next" class="icon-btn !size-9 !border-0 !bg-transparent" title="Next">
                    <i class="bi bi-skip-end-fill text-lg"></i>
                </button>

                <button type="button" data-player-action="repeat" class="icon-btn !size-9 !border-0 !bg-transparent" title="Repeat">
                    <i class="bi bi-repeat"></i>
                </button>
            </div>
        </div>

        {{-- Progress + volume --}}
        <div class="flex items-center gap-3">
            <span id="player-current-time" class="w-10 shrink-0 text-right text-xs font-bold tabular-nums text-slate-500 dark:text-slate-400">0:00</span>
            <input type="range" id="player-progress" class="range flex-1" min="0" max="100" step="0.1" value="0"/>
            <span id="player-total-time" class="w-10 shrink-0 text-xs font-bold tabular-nums text-slate-500 dark:text-slate-400">--:--</span>

            <span class="hidden items-center gap-2 sm:flex">
                <button type="button" id="player-mute" class="icon-btn !size-8 !border-0 !bg-transparent" title="Mute">
                    <i class="bi bi-volume-up-fill"></i>
                </button>
                <input type="range" id="player-volume" class="range w-24" min="0" max="100" step="1" value="100"/>
            </span>
        </div>
    </div>
</div>
