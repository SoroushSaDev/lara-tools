<?php

namespace App\Http\Controllers;

use App\Models\Music;
use App\Services\MusicLibraryService;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Native\Laravel\Dialog;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Local music library: importing, listing, streaming and removing tracks.
 */
class MusicController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected MusicLibraryService $library,
    ) {}

    /**
     * Display the music library, narrowed by the search term when present.
     */
    public function index(Request $request): View
    {
        $term = Search::term($request->query('q'));

        $tracks = Music::latest()->get();

        $data = [
            'musicFiles' => Search::filter($tracks, $term),
            'total' => $tracks->count(),
            'term' => $term,
            'query' => $request->query('q'),
        ];

        return $request->header('Turbo-Frame') === 'music-results'
            ? view('music.partial.results', $data)
            : view('music.index', $data);
    }

    /**
     * Open a native file picker and import everything selected.
     *
     * The route is POST-only and the form must carry the explicit "pick"
     * intent marker, so no stray request (hover prefetch included) can ever
     * open a native dialog on its own.
     */
    public function pick(Request $request): RedirectResponse
    {
        $request->validate(['intent' => ['required', 'string', 'in:pick']]);

        $selected = Dialog::new()
            ->title('Select music files or a folder')
            ->properties(['openFile', 'openDirectory', 'multiSelections'])
            ->filter('Audio Files', ['mp3', 'wav', 'flac', 'ogg', 'aac'])
            ->open();

        if (empty($selected)) {
            return back()->with('info', 'No files were selected.');
        }

        $imported = $this->library->importPaths((array) $selected);

        return redirect()
            ->route('music.index')
            ->with($imported > 0 ? 'success' : 'info', $imported > 0
                ? "Imported {$imported} ".str('track')->plural($imported).'.'
                : 'Nothing new to import.');
    }

    /**
     * Stream an audio file with HTTP range support so seeking works.
     */
    public function stream(Music $music): StreamedResponse
    {
        abort_unless($music->audioExists(), 404);

        return $this->streamFile(
            storage_path('app/public/'.$music->path),
            $this->mimeType($music->path),
        );
    }

    /**
     * Serve extracted cover artwork for a track.
     */
    public function cover(Music $music)
    {
        abort_unless($music->coverExists(), 404);

        return response()->file(storage_path('app/public/'.$music->cover_image));
    }

    /**
     * Build a range-aware streamed response for a local file.
     */
    protected function streamFile(string $path, string $mime): StreamedResponse
    {
        $size = filesize($path);
        $start = 0;
        $end = $size - 1;
        $status = 200;

        if (preg_match('/bytes=(\d*)-(\d*)/', request()->header('Range', ''), $matches)) {
            $start = $matches[1] !== '' ? (int) $matches[1] : 0;

            if ($matches[2] !== '') {
                $end = min((int) $matches[2], $size - 1);
            }

            $status = 206;
        }

        $length = $end - $start + 1;
        $handle = fopen($path, 'rb');
        fseek($handle, $start);

        return response()->stream(function () use ($handle, $length): void {
            // Stream in 64 KB chunks to keep memory usage flat.
            while ($length > 0 && ! feof($handle)) {
                $chunk = fread($handle, min(65536, $length));

                if ($chunk === false) {
                    break;
                }

                $length -= strlen($chunk);
                echo $chunk;
                flush();
            }

            fclose($handle);
        }, $status, [
            'Content-Type' => $mime,
            'Content-Length' => $length,
            'Accept-Ranges' => 'bytes',
            ...($status === 206 ? ['Content-Range' => "bytes {$start}-{$end}/{$size}"] : []),
        ]);
    }

    /**
     * Show the metadata editor for one track.
     */
    public function edit(Music $music): View
    {
        return view('music.edit', ['track' => $music]);
    }

    /**
     * Save editable metadata, writing it into the audio file when the format
     * can carry it.
     */
    public function update(Request $request, Music $music): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'album' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'between:0,9999'],
            'track_number' => ['nullable', 'integer', 'between:0,999'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'remove_cover' => ['nullable', 'boolean'],
        ]);

        $meta = collect($validated)
            ->only(['title', 'artist', 'album', 'genre', 'year', 'track_number'])
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all();

        $staleCover = $music->cover_image;
        $picture = $this->stageCover($request, $music);

        // The library row goes first: if it is rejected, the audio file and
        // the old artwork are still exactly as they were.
        $music->fill($meta)->save();

        if ($music->cover_image !== $staleCover) {
            $this->library->deleteCover($staleCover);
        }

        $written = $this->library->writeTags($music, $meta, $picture);

        return redirect()
            ->route('music.index')
            ->with(...$this->saveNotice($music, $written));
    }

    /**
     * Point the track at new artwork, or clear it, and return what to embed.
     *
     * The replaced file is only deleted once the row is saved, so a failed
     * save cannot leave the track pointing at artwork that no longer exists.
     *
     * @return array{data: string, mime: string}|null
     */
    protected function stageCover(Request $request, Music $music): ?array
    {
        if (! $file = $request->file('cover')) {
            if ($request->boolean('remove_cover')) {
                $music->cover_image = null;
            }

            return null;
        }

        $extension = $file->extension() === 'png' ? 'png' : 'jpg';
        $music->cover_image = $file->storeAs('covers', Str::random(12).'.'.$extension, 'public');

        return [
            'data' => (string) file_get_contents(storage_path('app/public/'.$music->cover_image)),
            'mime' => $extension === 'png' ? 'image/png' : 'image/jpeg',
        ];
    }

    /**
     * Flash pair describing what happened to the file itself.
     *
     * @return array{0: string, 1: string}
     */
    protected function saveNotice(Music $music, bool $written): array
    {
        if ($written) {
            return ['success', 'Saved — the tags are written into the MP3.'];
        }

        if (! $music->audioExists()) {
            return ['info', 'Saved in your library. The audio file is missing, so nothing was rewritten.'];
        }

        if (! $music->tagsAreWritable()) {
            return ['info', 'Saved in your library. Only MP3 tags can be rewritten, so the file keeps its own tags.'];
        }

        return ['error', 'Saved in your library, but the file could not be re-tagged.'];
    }

    /**
     * Soft-delete the specified track (restorable from the recycle bin).
     */
    public function destroy(Music $music): RedirectResponse
    {
        $music->delete();

        return redirect()
            ->route('music.index')
            ->with('success', 'Track moved to the recycle bin.');
    }

    /**
     * Guess the MIME type from a file extension.
     */
    protected function mimeType(string $file): string
    {
        return match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'flac' => 'audio/flac',
            'ogg' => 'audio/ogg',
            'aac' => 'audio/aac',
            default => 'application/octet-stream',
        };
    }
}
