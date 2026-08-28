<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A city saved by the user together with a snapshot of its weather.
 */
class City extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'main',
        'icon',
        'description',
        'coordinates',
        'temperature',
        'country_code',
        'feels_like',
        'humidity',
        'pressure',
        'wind_speed',
        'wind_direction',
        'sunrise',
        'sunset',
        'sea_level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'coordinates' => 'array',
            'sunrise' => 'datetime',
            'sunset' => 'datetime',
        ];
    }

    /**
     * Determine whether this city is configured as the user's home town.
     */
    public function isHomeTown(): bool
    {
        return app(\App\Services\SettingService::class)->get('home-town') === $this->name;
    }
}
