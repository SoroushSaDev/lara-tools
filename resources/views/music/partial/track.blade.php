{{-- One library row; doubles as a play button and carries track metadata --}}
<div @class([
    'track glass glass-hover group flex cursor-pointer items-center gap-3.5 p-3 sm:p-4',
    // Rows rendered by a search reload appear in place, so they skip the
    // staggered entrance that a first visit uses.
    'animate-fade-up' => ! ($instant ?? false),
])
     data-src="{{ $track->streamUrl() }}"
     data-ok="{{ $track->audioExists() ? '1' : '0' }}"
     data-title="{{ $track->displayTitle() }}"
     data-artist="{{ $track->artist ?? '' }}"
     data-cover="{{ $track->coverExists() ? $track->coverUrl() : '' }}"
     data-duration="{{ $track->duration ?? 0 }}">
    {{-- Artwork with play/eq overlay --}}
    <span class="relative flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-pink-400 to-fuchsia-600 text-xl text-white shadow-md sm:size-14">
        @if ($track->coverExists())
            <img src="{{ $track->coverUrl() }}" alt="" class="absolute inset-0 size-full object-cover"/>
        @else
            <i class="bi bi-music-note-beamed transition-transform duration-300 group-hover:scale-110"></i>
        @endif

        {{-- Animated equalizer while this row is playing --}}
        <span class="eq absolute inset-0 hidden items-end justify-center gap-[3px] bg-gradient-to-t from-fuchsia-700/80 to-pink-500/60 pb-2.5">
            <span class="w-1 origin-bottom rounded-full bg-white animate-eq-1 h-3"></span>
            <span class="w-1 origin-bottom rounded-full bg-white animate-eq-2 h-4"></span>
            <span class="w-1 origin-bottom rounded-full bg-white animate-eq-3 h-3"></span>
        </span>
    </span>

    <div class="relative min-w-0 flex-1 overflow-hidden">
        <h2 class="truncate font-bold sm:text-lg">{{ $track->displayTitle() }}</h2>

        <div class="mt-0.5 flex items-center gap-1 text-xs font-bold text-slate-500 dark:text-slate-400 sm:text-sm">
            <span class="truncate">{{ $track->artist ?? 'Unknown artist' }}</span>

            @if ($track->album)
                <span class="opacity-50">·</span>
                <span class="truncate">{{ $track->album }}</span>
            @endif

            @if ($track->genre)
                <span class="opacity-50">·</span>
                <span class="truncate">{{ $track->genre }}</span>
            @endif
        </div>
    </div>

    @unless ($track->audioExists())
        <span class="chip shrink-0 bg-amber-500/15 text-amber-600 dark:text-amber-300" title="Audio file is missing — re-import this track">
            <i class="bi bi-exclamation-triangle-fill"></i><span class="hidden sm:inline">Missing</span>
        </span>
    @endunless

    <span class="shrink-0 text-xs font-bold tabular-nums text-slate-400 dark:text-slate-500 sm:text-sm">
        {{ formatTime($track->duration) }}
    </span>

    {{-- Details editor and delete, both reachable without hovering --}}
    <a href="{{ route('music.edit', $track) }}" title="Edit details"
       class="icon-btn !size-9 shrink-0 opacity-100" data-turbo-prefetch="false">
        <i class="bi bi-pencil"></i>
    </a>

    <form method="POST" action="{{ route('music.destroy', $track) }}" class="shrink-0">
        @csrf
        @method('DELETE')

        <button type="submit" data-confirm="Move this track to the recycle bin?"
                class="icon-btn icon-btn-danger !size-9 opacity-100"
                title="Remove track">
            <i class="bi bi-trash3"></i>
        </button>
    </form>
</div>
