{{-- Library rows, re-rendered inside their own frame while the term changes.
     target="_top" keeps the Clear/empty-state links full-page. --}}
<turbo-frame id="music-results" target="_top">
    @if (filled($term))
        <p class="mb-3 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-sm font-bold text-slate-500 dark:text-slate-400">
            <span>
                <i class="bi bi-search me-1.5"></i>{{ $musicFiles->count() }} of {{ $total }} {{ str('track')->plural($total) }} match “{{ $query }}”
            </span>

            <a href="{{ route('music.index') }}" class="text-violet-600 transition-colors hover:text-violet-800 dark:text-fuchsia-300 dark:hover:text-fuchsia-200">
                Clear
            </a>
        </p>
    @endif

    <div class="grid grid-cols-1 gap-3 pb-24 xl:gap-4">
        @forelse ($musicFiles as $music)
            @include('music.partial.track', ['track' => $music, 'instant' => filled($term)])
        @empty
            @if (filled($term))
                <x-empty-state icon="bi-search" title="No track matches that">
                    <x-slot:description>
                        Nothing in your titles, artists, albums, genres or file names contains “{{ $query }}”.
                    </x-slot:description>

                    <x-slot:action>
                        <a href="{{ route('music.index') }}" class="btn-primary mt-2">
                            <i class="bi bi-x-lg"></i>Show the whole library
                        </a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <x-empty-state icon="bi-music-note-list" title="Your library is empty">
                    <x-slot:description>Import audio files or whole folders from your disk.</x-slot:description>

                    <x-slot:action>
                        <form method="POST" action="{{ route('music.pick') }}" class="mt-2" data-turbo-prefetch="false">
                            @csrf
                            <input type="hidden" name="intent" value="pick"/>

                            <button type="submit" class="btn-primary">
                                <i class="bi bi-plus-lg"></i>Add music
                            </button>
                        </form>
                    </x-slot:action>
                </x-empty-state>
            @endif
        @endforelse
    </div>
</turbo-frame>
