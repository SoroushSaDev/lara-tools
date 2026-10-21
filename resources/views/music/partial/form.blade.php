{{-- Shared track metadata form (expects $track and $action) --}}
<form id="meta-form" method="POST" action="{{ $action }}" enctype="multipart/form-data"
      class="grid grid-cols-1 animate-fade-up gap-4 lg:grid-cols-[minmax(0,1fr)_19rem] lg:items-start lg:gap-6">
    @csrf
    @method('PUT')

    <div class="flex flex-col gap-4">
        <div>
            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Title
            </label>

            <input type="text" name="title" value="{{ old('title', $track->title) }}" autocomplete="off"
                   class="field-input !text-lg" placeholder="{{ $track->displayTitle() }}"/>

            @error('title')
                <span class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Artist
                </label>

                <input type="text" name="artist" value="{{ old('artist', $track->artist) }}" autocomplete="off"
                       class="field-input" placeholder="Unknown artist"/>

                @error('artist')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Album
                </label>

                <input type="text" name="album" value="{{ old('album', $track->album) }}" autocomplete="off"
                       class="field-input" placeholder="Untitled album"/>

                @error('album')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Genre
                </label>

                <input type="text" name="genre" value="{{ old('genre', $track->genre) }}" autocomplete="off"
                       class="field-input" placeholder="Pop"/>

                @error('genre')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Year
                </label>

                <input type="number" name="year" min="0" max="9999" step="1"
                       value="{{ old('year', $track->year) }}" class="field-input tabular-nums" placeholder="2024"/>

                @error('year')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Track number
                </label>

                <input type="number" name="track_number" min="0" max="999" step="1"
                       value="{{ old('track_number', $track->track_number) }}" class="field-input tabular-nums"
                       placeholder="1"/>

                @error('track_number')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <p class="text-xs font-semibold leading-relaxed text-slate-500 dark:text-slate-400">
            @if ($track->tagsAreWritable())
                <i class="bi bi-magic me-1 text-violet-500"></i>Saving rewrites this MP3's own ID3 tag, so other players
                see the same titles. Leaving a box empty clears that tag.
            @else
                <i class="bi bi-info-circle me-1 text-sky-500"></i>This file is not an MP3, so its own tags cannot be
                rewritten — the details are saved in your library and shown everywhere in the app.
            @endif
        </p>
    </div>

    <aside class="glass flex flex-col gap-3 p-4">
        <h3 class="heading flex items-center gap-2 text-sm">
            <i class="bi bi-image text-fuchsia-500"></i>Cover art
        </h3>

        <span class="relative flex aspect-square w-full max-w-56 items-center justify-center self-center overflow-hidden rounded-xl bg-gradient-to-br from-pink-400 to-fuchsia-600 text-3xl text-white shadow-md lg:max-w-none">
            @if ($track->coverExists())
                <img src="{{ $track->coverUrl() }}" alt="" class="absolute inset-0 size-full object-cover"/>
            @else
                <i class="bi bi-music-note-beamed"></i>
            @endif
        </span>

        <div>
            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                   for="cover">
                Replace with an image
            </label>

            <input type="file" id="cover" name="cover" accept="image/jpeg,image/png" class="field-input text-sm"/>

            @error('cover')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <label class="flex cursor-pointer items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
            <input type="checkbox" name="remove_cover" value="1" class="size-4 rounded border-slate-400/40"/>
            Remove the current cover
        </label>

        <p class="text-xs font-semibold leading-relaxed text-slate-500 dark:text-slate-400">
            JPEG or PNG, up to 5 MB. New artwork is embedded in the file too, when the format allows it.
        </p>
    </aside>
</form>
