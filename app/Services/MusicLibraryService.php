<?php

namespace App\Services;

use App\Models\Music;
use getID3;
use getid3_lib;
use getid3_write_id3v2;
use getid3_writetags;
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
     * @return array{0: array{title: string|null, artist: string|null, album: string|null, genre: string|null, year: int|null, track_number: int|null, duration: int|null}, 1: string|null}
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

        $text = fn (string $key): ?string => $this->limitStr(
            trim((string) ($info['comments'][$key][0] ?? '')),
        );

        return [
            [
                'title' => $text('title'),
                'artist' => $text('artist'),
                'album' => $text('album'),
                'genre' => $text('genre'),
                'year' => $this->number($info['comments']['year'][0] ?? null),
                'track_number' => $this->number($info['comments']['track_number'][0] ?? null),
                'duration' => isset($info['playtime_seconds']) ? (int) round($info['playtime_seconds']) : null,
            ],
            $this->storeCoverArt($info),
        ];
    }

    /**
     * First number inside a tag value, so "3/12" reads as track 3.
     */
    protected function number(?string $value): ?int
    {
        if ($value === null || ! preg_match('/\d{1,4}/', $value, $matches)) {
            return null;
        }

        return (int) $matches[0];
    }

    /**
     * Trim a tag to the column width, dropping blank values.
     */
    protected function limitStr(string $value, int $max = 255): ?string
    {
        return $value === '' ? null : Str::limit($value, $max, '');
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
     * Rewrite the tag block of a stored track.
     *
     * The library copy is rebuilt beside itself and only swapped in once it
     * analyses cleanly, so a failed write can never damage the audio.
     *
     * @param  array<string, string|int|null>  $meta
     * @param  array{data: string, mime: string}|null  $picture
     */
    public function writeTags(Music $music, array $meta, ?array $picture = null): bool
    {
        $absolute = storage_path('app/public/'.$music->path);

        if (! $music->tagsAreWritable() || ! is_file($absolute) || ! is_writable($absolute)) {
            return false;
        }

        $staging = $absolute.'.id3tmp';

        if (! @copy($absolute, $staging)) {
            return false;
        }

        try {
            // The tag writer only loads once getID3's own library file has run
            // (it refuses to work without GETID3_INCLUDEPATH), so resolve it
            // before asking the autoloader for the writer.
            class_exists(getID3::class);

            $payload = $this->tagPayload($meta, $this->coverFor($music, $picture));

            if ($payload === []) {
                // Nothing left to write means every field was cleared, and
                // getID3 refuses an empty tag set — strip the block instead.
                $stripper = new getid3_write_id3v2;
                $stripper->filename = $staging;
                $stripper->RemoveID3v2();
            } else {
                $writer = new getid3_writetags;
                $writer->filename = $staging;
                $writer->tagformats = ['id3v2.3'];
                $writer->tag_encoding = 'UTF-8';
                $writer->overwrite_tags = true;
                $writer->remove_other_tags = false;
                $writer->tag_data = $payload;

                if (! $writer->WriteTags()) {
                    return false;
                }
            }

            if (! $this->rewrittenOk($staging, $meta['title'] ?? null)) {
                return false;
            }

            return @rename($staging, $absolute);
        } catch (Throwable) {
            return false;
        } finally {
            if (is_file($staging)) {
                @unlink($staging);
            }
        }
    }

    /**
     * Build the getID3 payload. Blank fields are omitted, which under
     * overwrite_tags removes them from the file.
     *
     * @param  array<string, string|int|null>  $meta
     * @param  array{data: string, mime: string}|null  $picture
     * @return array<string, mixed>
     */
    protected function tagPayload(array $meta, ?array $picture): array
    {
        $payload = [];

        foreach (['TITLE' => 'title', 'ARTIST' => 'artist', 'ALBUM' => 'album', 'GENRE' => 'genre'] as $frame => $field) {
            $value = trim((string) ($meta[$field] ?? ''));

            if ($value !== '') {
                $payload[$frame] = [$value];
            }
        }

        foreach (['YEAR' => 'year', 'TRACKNUMBER' => 'track_number'] as $frame => $field) {
            if (filled($meta[$field] ?? null)) {
                $payload[$frame] = [(string) $meta[$field]];
            }
        }

        if ($picture !== null) {
            $payload['ATTACHED_PICTURE'] = [[
                'data' => $picture['data'],
                'mime' => $picture['mime'],
                'picturetypeid' => 3,
                'description' => '',
                'encodingid' => 0,
            ]];
        }

        return $payload;
    }

    /**
     * Artwork to embed: the uploaded image, otherwise whatever the library
     * already stores, so a text-only edit never drops existing art.
     *
     * @param  array{data: string, mime: string}|null  $picture
     * @return array{data: string, mime: string}|null
     */
    protected function coverFor(Music $music, ?array $picture): ?array
    {
        if ($picture !== null) {
            return $picture;
        }

        if (! $music->coverExists()) {
            return null;
        }

        $binary = @file_get_contents(storage_path('app/public/'.$music->cover_image));

        if ($binary === false) {
            return null;
        }

        return [
            'data' => $binary,
            'mime' => str_ends_with($music->cover_image, '.png') ? 'image/png' : 'image/jpeg',
        ];
    }

    /**
     * Confirm the staged file still decodes as MP3 and carries the new title.
     */
    protected function rewrittenOk(string $path, ?string $expectedTitle): bool
    {
        try {
            $info = (new getID3)->analyze($path);
            getid3_lib::CopyTagsToComments($info);
        } catch (Throwable) {
            return false;
        }

        if (! empty($info['fatalerror']) || ($info['audio']['dataformat'] ?? null) !== 'mp3') {
            return false;
        }

        $title = trim((string) ($info['comments']['title'][0] ?? ''));
        $expected = trim((string) $expectedTitle);

        return $expected === '' ? $title === '' : $title === $expected;
    }

    /**
     * Delete a stored artwork file by its relative path.
     */
    public function deleteCover(?string $relativePath): void
    {
        if (filled($relativePath) && File::exists($absolute = storage_path('app/public/'.$relativePath))) {
            File::delete($absolute);
        }
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
