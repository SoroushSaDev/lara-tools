<?php

namespace App\Services;

use App\Models\City;
use App\Models\Event;
use App\Models\Music;
use App\Models\Note;
use App\Models\Todo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Single place that knows which models support the Recycle Bin,
 * how to list their trashed rows and how to restore or purge them.
 */
class RecycleBinService
{
    /**
     * Map of bin "type" slugs to their model classes.
     *
     * @var array<string, class-string<Model>>
     */
    protected const TYPES = [
        'notes' => Note::class,
        'todos' => Todo::class,
        'events' => Event::class,
        'cities' => City::class,
        'music' => Music::class,
    ];

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected MusicLibraryService $musicLibrary,
    ) {}

    /**
     * List every trashed record grouped by type.
     *
     * @return array<string, array{model: class-string<Model>, label: string, icon: string, items: Collection}>
     */
    public function groupedTrash(): array
    {
        return collect(self::TYPES)
            ->map(fn (string $model) => [
                'model' => $model,
                'label' => match ($model) {
                    Note::class => 'Notes',
                    Todo::class => 'Todos',
                    Event::class => 'Events',
                    City::class => 'Cities',
                    default => 'Tracks',
                },
                'icon' => match ($model) {
                    Note::class => 'bi-journal-text',
                    Todo::class => 'bi-list-check',
                    Event::class => 'bi-calendar-event',
                    City::class => 'bi-geo-alt',
                    default => 'bi-music-note-beamed',
                },
                'items' => $model::onlyTrashed()->latest('deleted_at')->get(),
            ])
            ->filter(fn (array $group) => $group['items']->isNotEmpty())
            ->all();
    }

    /**
     * Restore a single trashed record by type and id.
     *
     * @return bool True when a record was restored.
     */
    public function restore(string $type, int $id): bool
    {
        $record = $this->modelFor($type)::onlyTrashed()->find($id);

        if (! $record) {
            return false;
        }

        $record->restore();

        return true;
    }

    /**
     * Permanently delete a trashed record, cleaning up any files.
     *
     * @return bool True when a record was purged.
     */
    public function purge(string $type, int $id): bool
    {
        $record = $this->modelFor($type)::onlyTrashed()->find($id);

        if (! $record) {
            return false;
        }

        if ($record instanceof Music) {
            $this->musicLibrary->deleteFiles($record);
        }

        $record->forceDelete();

        return true;
    }

    /**
     * Permanently empty every trash bin.
     *
     * @return int Number of records removed.
     */
    public function emptyAll(): int
    {
        $removed = 0;

        foreach (self::TYPES as $model) {
            $model::onlyTrashed()->get()->each(function ($record) use (&$removed) {
                if ($record instanceof Music) {
                    $this->musicLibrary->deleteFiles($record);
                }

                $record->forceDelete();
                $removed++;
            });
        }

        return $removed;
    }

    /**
     * Whether the given bin type slug is supported.
     */
    public function handles(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    /**
     * Resolve the model class behind a bin type slug.
     *
     * @return class-string<Model>
     */
    public function modelFor(string $type): string
    {
        return self::TYPES[$type] ?? throw new \InvalidArgumentException("Unknown recycle-bin type [{$type}].");
    }
}
