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
     * The injected key is kept as given: it has to stay tellable apart from
     * the one read out of `services.openweather.key`.
     *
     * @param  SettingRepository|null  $settings  Provides the preferred measurement unit and the user's API key.
     */
    public function __construct(
        protected ?string $apiKey = null,
        protected ?string $baseUrl = null,
        protected ?SettingRepository $settings = null,
    ) {
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
     * {@inheritDoc}
     */
    public function isConfigured(): bool
    {
        return filled($this->resolveKey());
    }

    /**
     * Which key to call the API with.
     *
     * An injected key wins (that is how tests and manual construction pin
     * one), then the one typed into Settings — the user's own choice — and
     * only last the environment value, which exists on development machines
     * but is stripped from every bundled app.
     */
    protected function resolveKey(): ?string
    {
        if (filled($this->apiKey)) {
            return $this->apiKey;
        }

        $stored = trim((string) ($this->settings->get('weather_api_key') ?? ''));

        if ($stored !== '') {
            return $stored;
        }

        $fromConfig = config('services.openweather.key');

        return filled($fromConfig) ? (string) $fromConfig : null;
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
        $key = $this->resolveKey();

        if (empty($key)) {
            throw new WeatherException('No OpenWeatherMap API key is configured.');
        }

        try {
            $response = Http::timeout(self::TIMEOUT)->get("{$this->baseUrl}/weather", array_merge($query, [
                'appid' => $key,
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
