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
        'duration',
        'cover_image',
        'tags',
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
            'tags' => 'array',
        ];
    }

    /**
     * Tags as a clean list of strings, oldest-first order preserved.
     *
     * @return list<string>
     */
    public function tagList(): array
    {
        return collect($this->tags ?? [])
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
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
