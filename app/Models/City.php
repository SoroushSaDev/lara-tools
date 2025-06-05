<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'deleted_at',
    ];

    /**
     * @return bool
     */
    public function isHomeTown(): bool
    {
        $result = false;
        $settings = Setting::where('key', 'home-town')?->first() ?? null;

        if ($settings) {
            $result = $settings->value == $this->name;
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getNext(): mixed
    {
        return self::firstWhere('id', $this->id + 1)?->id ?? 0;
    }

    /**
     * @return mixed
     */
    public function getLast(): mixed
    {
        return self::firstWhere('id', $this->id - 1)?->id ?? 0;
    }
}
