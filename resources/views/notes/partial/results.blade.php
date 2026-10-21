{{-- Notes list, re-rendered inside its own frame while the search term changes.
     target="_top": the note cards are links, and a frame would otherwise
     capture them and find no matching frame in /notes/{id}. --}}
<turbo-frame id="notes-results" target="_top">
    @if (filled($term))
        <p class="mb-4 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-sm font-bold text-slate-500 dark:text-slate-400">
            <span>
                <i class="bi bi-search me-1.5"></i>{{ $notes->count() }} of {{ $total }} {{ str('note')->plural($total) }} match “{{ $query }}”
            </span>

            <a href="{{ route('notes.index') }}" class="text-violet-600 transition-colors hover:text-violet-800 dark:text-fuchsia-300 dark:hover:text-fuchsia-200">
                Clear
            </a>
        </p>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($notes as $note)
            <a href="{{ route('notes.show', $note) }}"
               @class([
                   'glass glass-hover group relative flex flex-col gap-3 p-5',
                   'animate-fade-up' => blank($term),
               ])
               style="animation-delay: {{ ($loop->index % 9) * 60 }}ms">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="heading truncate text-lg leading-snug">{{ $note->title ?? 'Untitled' }}</h2>

                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-amber-400/20 to-orange-500/20 text-amber-500 opacity-0 transition-opacity duration-200 group-hover:opacity-100 dark:text-orange-300">
                        <i class="bi bi-arrow-right"></i>
                    </span>
                </div>

                <p class="line-clamp-4 whitespace-pre-line text-sm font-semibold leading-relaxed text-slate-600 dark:text-slate-400">
                    {{ App\Support\RichText::toPlainText($note->body, $excerptLength) }}
                </p>

                <p class="mt-auto flex items-center gap-1.5 pt-2 text-xs font-bold text-slate-400 dark:text-slate-500">
                    <i class="bi bi-clock"></i>
                    {{ $note->updated_at?->diffForHumans() }}
                </p>
            </a>
        @empty
            <div class="col-span-full py-6">
                @if (filled($term))
                    <x-empty-state icon="bi-search" title="No note matches that">
                        <x-slot:description>Nothing in your titles or note text contains “{{ $query }}”.</x-slot:description>

                        <x-slot:action>
                            <a href="{{ route('notes.index') }}" class="btn-primary mt-2">
                                <i class="bi bi-x-lg"></i>Show all notes
                            </a>
                        </x-slot:action>
                    </x-empty-state>
                @else
                    <x-empty-state icon="bi-journal-x" title="No notes yet">
                        <x-slot:description>Capture your first thought — it will live here.</x-slot:description>

                        <x-slot:action>
                            <a href="{{ route('notes.create') }}" class="btn-primary mt-2">
                                <i class="bi bi-plus-lg"></i>Create a note
                            </a>
                        </x-slot:action>
                    </x-empty-state>
                @endif
            </div>
        @endforelse
    </div>
</turbo-frame>
