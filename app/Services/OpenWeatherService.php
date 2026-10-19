<?php

namespace App\Services;

use App\Contracts\SettingRepository;
use App\Contracts\WeatherService as WeatherServiceContract;
use App\Exceptions\WeatherException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenWeatherMap implementation of the weather contract.
 */
class OpenWeatherService implements WeatherServiceContract
{
    /**
     * HTTP timeout in seconds for API calls.
     *
     * @var int
     */
    protected const TIMEOUT = 10;

    /**
     * Create a new service instance.
     *
     * All parameters fall back to configuration so the service is usable
     * both through the container and when constructed manually in tests.
     *
     * @param  SettingRepository|null  $settings  Provides the preferred measurement unit.
     */
    public function __construct(
        protected ?string $apiKey = null,
        protected ?string $baseUrl = null,
        protected ?SettingRepository $settings = null,
    ) {
        $this->apiKey ??= config('services.openweather.key');
        $this->baseUrl ??= config('services.openweather.base_url', 'https://api.openweathermap.org/data/2.5');
        $this->settings ??= app(SettingRepository::class);
        $this->baseUrl = rtrim($this->baseUrl, '/');
    }

    /**
     * {@inheritDoc}
     */
    public function byCity(string $city, ?string $units = null): array
    {
        return $this->fetch(['q' => $city], $units);
    }

    /**
     * {@inheritDoc}
     */
    public function byCoordinates(float $latitude, float $longitude, ?string $units = null): array
    {
        return $this->fetch(['lat' => $latitude, 'lon' => $longitude], $units);
    }

    /**
     * Resolve the unit system configured by the user.
     */
    protected function defaultUnits(): string
    {
        return (string) ($this->settings->get('weather_units')
            ?? config('services.openweather.units', 'metric'));
    }

    /**
     * Perform the API request and normalise the response.
     *
     * @param  array<string, mixed>  $query
     *
     * @throws WeatherException
     */
    protected function fetch(array $query, ?string $units): array
    {
        if (empty($this->apiKey)) {
            throw new WeatherException('No OpenWeatherMap API key is configured.');
        }

        try {
            $response = Http::timeout(self::TIMEOUT)->get("{$this->baseUrl}/weather", array_merge($query, [
                'appid' => $this->apiKey,
                'units' => $units ?? $this->defaultUnits(),
            ]));
        } catch (ConnectionException $exception) {
            Log::warning('OpenWeather request timed out', ['query' => $query]);

            throw new WeatherException('Could not reach the weather service. Check your internet connection.', previous: $exception);
        }

        if ($response->status() === 404) {
            throw WeatherException::unknownLocation((string) ($query['q'] ?? 'the given coordinates'));
        }

        if ($response->failed()) {
            Log::warning('OpenWeather request failed', ['status' => $response->status()]);

            throw new WeatherException('The weather service returned an error. Please try again later.');
        }

        return $response->json();
    }
}
