<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A user-created calendar event.
 */
class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'address',
        'starts_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
        ];
    }
}
