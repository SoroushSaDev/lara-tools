<?php

namespace App\Contracts;

use App\Exceptions\WeatherException;

/**
 * Contract for fetching current weather conditions.
 */
interface WeatherService
{
    /**
     * Fetch current weather for a city name.
     *
     * @param  string|null  $units  "metric" or "imperial"
     * @return array Normalised weather payload.
     *
     * @throws WeatherException When the city cannot be resolved or the API fails.
     */
    public function byCity(string $city, ?string $units = null): array;

    /**
     * Fetch current weather for geographic coordinates.
     *
     * @param  string|null  $units  "metric" or "imperial"
     * @return array Normalised weather payload.
     *
     * @throws WeatherException When the coordinates cannot be resolved or the API fails.
     */
    public function byCoordinates(float $latitude, float $longitude, ?string $units = null): array;

    /**
     * Determine whether a provider key is available.
     *
     * Lets a view say "no key configured" instead of reporting an untried
     * lookup as a connection failure.
     */
    public function isConfigured(): bool;
}
