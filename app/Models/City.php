<?php

namespace App\Models;

use App\Contracts\SettingRepository;
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
        return app(SettingRepository::class)->get('home-town') === $this->name;
    }

    /**
     * Present the stored snapshot in the shape of an API payload.
     *
     * Weather is read from the network, so any request can fail; the dashboard
     * renders one view either way, and this is what keeps it populated when
     * there is no connection. Only the columns actually stored are emitted —
     * the view hides a property whose key is missing, which is why there is no
     * gust, cloud cover or visibility here. Sunrise and sunset are deliberately
     * left out too: the view falls back to the datetime columns and labels them
     * "stored snapshot", whereas a faked timestamp would be shifted by an offset
     * this table never kept.
     *
     * @return array<string, mixed>
     */
    public function snapshotPayload(): array
    {
        $main = array_filter([
            'temp' => is_numeric($this->temperature) ? (float) $this->temperature : null,
            'feels_like' => is_numeric($this->feels_like) ? (float) $this->feels_like : null,
            'humidity' => is_numeric($this->humidity) ? (int) $this->humidity : null,
            'pressure' => is_numeric($this->pressure) ? (float) $this->pressure : null,
            'sea_level' => is_numeric($this->sea_level) && $this->sea_level > 0 ? (float) $this->sea_level : null,
        ]);

        $wind = array_filter([
            'speed' => is_numeric($this->wind_speed) ? (float) $this->wind_speed : null,
            'deg' => is_numeric($this->wind_direction) ? (float) $this->wind_direction : null,
        ]);

        $coord = array_filter([
            'lat' => data_get($this->coordinates, 'lat'),
            'lon' => data_get($this->coordinates, 'lon'),
        ]);

        return [
            'name' => $this->name,
            'weather' => [[
                'main' => $this->main,
                'description' => $this->description,
                'icon' => $this->icon,
            ]],
            'main' => $main,
            'sys' => ['country' => $this->country_code],
        ]
            + ($coord !== [] ? ['coord' => $coord] : [])
            + ($wind !== [] ? ['wind' => $wind] : []);
    }
}
