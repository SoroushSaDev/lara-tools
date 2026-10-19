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
        'latitude',
        'longitude',
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
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Whether the event carries map coordinates from the location picker.
     */
    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
