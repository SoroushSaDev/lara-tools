<?php

namespace App\Services;

use App\Models\Music;
use getID3;
use getid3_lib;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

/**
 * Handles importing audio files into the local library
 * and removing them again, including artwork and metadata.
 */
class MusicLibraryService
{
    /**
     * File extensions accepted by the importer.
     *
     * @var list<string>
     */
    protected const ALLOWED_EXTENSIONS = ['mp3', 'wav', 'flac', 'ogg', 'aac'];

    /**
     * Import one or many paths (files or folders) into the library.
     *
     * @param  array<int, string>  $paths
     * @return int Number of tracks imported.
     */
    public function importPaths(array $paths): int
    {
        $imported = 0;

        foreach ($paths as $path) {
            if (is_dir($path)) {
                $imported += $this->importDirectory($path);

                continue;
            }

            if ($this->importFile($path)) {
                $imported++;
            }
        }

        return $imported;
    }

    /**
     * Recursively import every supported audio file inside a directory.
     *
     * @return int Number of tracks imported.
     */
    protected function importDirectory(string $directory): int
    {
        $imported = 0;

        foreach (File::allFiles($directory) as $file) {
            if ($this->importFile($file->getRealPath())) {
                $imported++;
            }
        }

        return $imported;
    }

    /**
     * Copy a single audio file into storage and persist its metadata.
     *
     * @return bool True when a new track was created.
     */
    protected function importFile(string $originalPath): bool
    {
        if (! in_array(strtolower(pathinfo($originalPath, PATHINFO_EXTENSION)), self::ALLOWED_EXTENSIONS, true)) {
            return false;
        }

        // Skip duplicates — but only when the previously imported copy is
        // still healthy. A record whose file vanished gets replaced by a
        // fresh import so re-adding a folder also repairs the library.
        $existing = Music::withTrashed()->where('name', basename($originalPath))->first();

        if ($existing) {
            if ($existing->audioExists()) {
                return false;
            }

            $existing->forceDelete();
        }

        $uniqueName = uniqid().'_'.basename($originalPath);
        $destination = 'music/'.$uniqueName;

        File::ensureDirectoryExists(storage_path('app/public/music'));

        if (! @File::copy($originalPath, storage_path('app/public/'.$destination))) {
            report(new \RuntimeException("Unable to copy audio file [{$originalPath}] into storage."));

            return false;
        }

        [$metadata, $coverPath] = $this->extractMetadata($originalPath);

        Music::create(array_merge([
            'name' => basename($originalPath),
            'path' => $destination,
            'cover_image' => $coverPath,
        ], $metadata));

        return true;
    }

    /**
     * Analyse an audio file and extract tag data plus embedded artwork.
     *
     * The source file is analysed directly so metadata extraction never
     * depends on where the track ends up being stored.
     *
     * @param  string  $sourcePath  Absolute path to the audio file.
     * @return array{0: array{title?: string|null, artist?: string|null, album?: string|null, duration?: int|null, tags?: list<string>|null}, 1: string|null}
     */
    protected function extractMetadata(string $sourcePath): array
    {
        try {
            $analyzer = new getID3;
            $info = $analyzer->analyze($sourcePath);
            getid3_lib::CopyTagsToComments($info);
        } catch (Throwable) {
            return [[], null];
        }

        $genre = trim((string) ($info['comments']['genre'][0] ?? ''));

        return [
            [
                'title' => $info['comments_html']['title'][0] ?? null,
                'artist' => $info['comments_html']['artist'][0] ?? null,
                'album' => $info['comments_html']['album'][0] ?? null,
                'duration' => isset($info['playtime_seconds']) ? (int) round($info['playtime_seconds']) : null,
                'tags' => $genre === '' ? null : [$genre],
            ],
            $this->storeCoverArt($info),
        ];
    }

    /**
     * Persist embedded album art to the covers directory.
     *
     * @param  array<string, mixed>  $info
     * @return string|null Relative path of the stored cover image.
     */
    protected function storeCoverArt(array $info): ?string
    {
        $picture = $info['id3v2']['APIC'][0] ?? null;

        if (empty($picture['data'])) {
            return null;
        }

        $extension = ($picture['image_mime'] ?? '') === 'image/png' ? 'png' : 'jpg';
        $relativePath = 'covers/'.Str::random(12).'.'.$extension;

        File::ensureDirectoryExists(storage_path('app/public/covers'));
        File::put(storage_path('app/public/'.$relativePath), $picture['data']);

        return $relativePath;
    }

    /**
     * Delete the stored audio file and cover art for a track.
     */
    public function deleteFiles(Music $music): void
    {
        foreach ([$music->path, $music->cover_image] as $relativePath) {
            if (filled($relativePath) && File::exists($absolute = storage_path('app/public/'.$relativePath))) {
                File::delete($absolute);
            }
        }
    }
}
