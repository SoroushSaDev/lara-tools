<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A checklist containing multiple toggleable items.
 */
class Todo extends Model
{
    use SoftDeletes;

    /**
     * Priority levels, ordered from least to most urgent.
     *
     * @var list<string>
     */
    public const PRIORITIES = ['low', 'medium', 'high'];

    protected $fillable = [
        'title',
        'description',
        'items',
        'due_at',
        'priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'due_at' => 'date',
        ];
    }

    /**
     * Get the checklist items, always normalised to a list of arrays.
     *
     * @return array<int, array{text: string, completed: bool}>
     */
    public function items(): array
    {
        return collect($this->items ?? [])
            ->map(fn ($item) => [
                'text' => (string) ($item['text'] ?? ''),
                'completed' => (bool) ($item['completed'] ?? false),
            ])
            ->values()
            ->all();
    }

    /**
     * Values the search boxes look through.
     *
     * Item text comes from the decoded list, because the stored JSON escapes
     * everything outside ASCII and Persian entries would never match.
     *
     * @return array<int, string|null>
     */
    public function searchFields(): array
    {
        return array_merge(
            [$this->title, $this->description],
            array_column($this->items(), 'text'),
        );
    }

    /**
     * Whether the list carries a real priority ('none' and unmigrated rows do not).
     */
    public function hasPriority(): bool
    {
        return in_array($this->priority, self::PRIORITIES, true);
    }

    /**
     * Whether the deadline has passed with items still open.
     */
    public function isOverdue(): bool
    {
        $progress = $this->progress();

        return $this->due_at !== null
            && $this->due_at->toDateString() < today()->toDateString()
            && $progress['completed'] < $progress['total'];
    }

    /**
     * Number of completed items versus total items.
     *
     * @return array{completed: int, total: int, percent: int}
     */
    public function progress(): array
    {
        $items = $this->items();
        $total = count($items);
        $completed = count(array_filter($items, fn ($item) => $item['completed']));

        return [
            'completed' => $completed,
            'total' => $total,
            'percent' => $total === 0 ? 0 : (int) round(($completed / $total) * 100),
        ];
    }
}
