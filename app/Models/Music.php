<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * An imported audio track with its metadata and artwork.
 */
class Music extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'path',
        'title',
        'artist',
        'album',
        'genre',
        'year',
        'track_number',
        'duration',
        'cover_image',
        'liked',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'year' => 'integer',
            'track_number' => 'integer',
            'liked' => 'boolean',
        ];
    }

    /**
     * Values the search boxes look through.
     *
     * @return array<int, string|int|null>
     */
    public function searchFields(): array
    {
        return [
            $this->displayTitle(),
            $this->artist,
            $this->album,
            $this->genre,
            $this->name,
            $this->year,
        ];
    }

    /**
     * Whether the stored file's own tag block can be rewritten.
     *
     * Only MP3 is handled: getID3 writes Vorbis/FLAC comments by shelling out
     * to metaflac and vorbiscomment, which this app does not bundle.
     */
    public function tagsAreWritable(): bool
    {
        return strtolower(pathinfo((string) $this->path, PATHINFO_EXTENSION)) === 'mp3';
    }

    /**
     * Whether the stored audio file is present on disk.
     */
    public function audioExists(): bool
    {
        return filled($this->path) && Storage::disk('public')->exists($this->path);
    }

    /**
     * Whether the extracted cover art file is present on disk.
     */
    public function coverExists(): bool
    {
        return filled($this->cover_image) && Storage::disk('public')->exists($this->cover_image);
    }

    /**
     * Display title, falling back to the file name when untitled.
     */
    public function displayTitle(): string
    {
        return $this->title ?: pathinfo($this->name, PATHINFO_FILENAME);
    }

    /**
     * Absolute URL to the streaming endpoint for this track.
     *
     * Bound by ID rather than file name so any legal file name streams,
     * not just names that happen to survive a strict route constraint.
     */
    public function streamUrl(): string
    {
        return route('music.stream', $this);
    }

    /**
     * Absolute URL to the cover-art endpoint (always resolvable;
     * the controller decides whether a file actually exists).
     */
    public function coverUrl(): string
    {
        return route('music.cover', $this);
    }
}
