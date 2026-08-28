<?php

namespace App\Http\Controllers;

use App\Models\Music;
use App\Services\MusicLibraryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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
     * Display the music library.
     */
    public function index(): View
    {
        return view('music.index', [
            'musicFiles' => Music::latest()->get(),
        ]);
    }

    /**
     * Open a native file picker and import everything selected.
     */
    public function pick(): RedirectResponse
    {
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
