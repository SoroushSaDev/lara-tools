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

    protected $fillable = [
        'title',
        'description',
        'items',
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
