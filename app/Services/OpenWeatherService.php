<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenWeatherService
{
    protected mixed $apiKey;
    protected mixed $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openweather.key');
        $this->baseUrl = config('services.openweather.base_url');
    }

    /**
     * @param string $city
     * @param string $units
     * @return array|mixed
     */
    public function getWeatherByCity(string $city, string $units = 'metric'): mixed
    {
        try {
            $response = Http::get("{$this->baseUrl}/weather", [
                'q' => $city,
                'appid' => $this->apiKey,
                'units' => $units,
            ])->json();
        } catch (\Exception $exception) {
            $response = [];
        }

        return $response;
    }

    /**
     * @param float $lat
     * @param float $lon
     * @param string $units
     * @return array|mixed
     */
    public function getWeatherByCoordinates(float $lat, float $lon, string $units = 'metric'): mixed
    {
        try {
            $response = Http::get("{$this->baseUrl}/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $this->apiKey,
                'units' => $units,
            ])->json();
        } catch (\Exception $exception) {
            $response = [];
        }

        return $response;
    }
}
