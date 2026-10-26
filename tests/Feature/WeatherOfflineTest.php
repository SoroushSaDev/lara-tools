<?php

namespace Tests\Feature;

use App\Contracts\SettingRepository;
use App\Contracts\WeatherService;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Weather is the only screen that depends on the network, so it has to keep
 * working when there is none: a saved city carries its last reading.
 */
class WeatherOfflineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.openweather.key' => 'test-key']);
    }

    protected function city(): City
    {
        return City::create([
            'name' => 'Tehran',
            'main' => 'Clear',
            'icon' => '01d',
            'description' => 'clear sky',
            'coordinates' => ['lat' => 35.69, 'lon' => 51.42],
            'temperature' => 21.5,
            'country_code' => 'IR',
            'feels_like' => 20.1,
            'humidity' => 41,
            'pressure' => 1013,
            'wind_speed' => 4.6,
            'wind_direction' => 250,
            'sunrise' => now()->setTime(6, 12),
            'sunset' => now()->setTime(17, 40),
            'sea_level' => 1013,
        ]);
    }

    public function test_saved_city_is_requested_by_its_coordinates(): void
    {
        $this->city();

        Http::fake(['*' => Http::response(['cod' => 200, 'name' => 'Tehran', 'main' => ['temp' => 30]])]);

        $this->get(route('weather.index', ['city' => 'Tehran']))->assertOk();

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), 'lat=35.69') && str_contains($request->url(), 'lon=51.42');
        });
    }

    public function test_dashboard_falls_back_to_the_stored_snapshot_when_offline(): void
    {
        $this->city();

        Http::fake(fn () => throw new ConnectionException('Connection refused.'));

        $response = $this->get(route('weather.index', ['city' => 'Tehran']));

        $response->assertOk()
            ->assertSee('Offline — showing the reading saved')
            ->assertSee('Could not reach the weather service')
            // These numbers come from the cities row, not from the API.
            ->assertSee('22°')
            ->assertSee('41%')
            ->assertSee('clear sky')
            ->assertDontSee('Weather data unavailable');
    }

    public function test_snapshot_sunrise_is_labelled_as_stored_data(): void
    {
        $this->city();

        Http::fake(fn () => throw new ConnectionException('Connection refused.'));

        // Sunrise and sunset stay out of the synthesised payload so the view
        // labels them rather than shifting them by an offset never stored.
        $this->get(route('weather.index', ['city' => 'Tehran']))
            ->assertSee('Sunrise')
            ->assertSee('stored snapshot');
    }

    public function test_live_location_has_no_snapshot_to_show(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection refused.'));

        $this->get(route('weather.locate', ['lat' => 35.69, 'lon' => 51.42]))
            ->assertOk()
            ->assertSee('Could not reach the weather service')
            ->assertDontSee('Offline — showing the reading saved');
    }

    public function test_a_missing_key_offers_the_setting_instead_of_blaming_the_network(): void
    {
        config(['services.openweather.key' => null]);

        $this->city();

        Http::fake();

        $this->get(route('weather.index', ['city' => 'Tehran']))
            ->assertOk()
            ->assertSee('No weather API key saved')
            ->assertSee('Add a key to refresh it')
            // Nothing timed out, so the offline wording would send the user
            // to the wrong fix.
            ->assertDontSee('Offline — showing the reading saved')
            // The stored numbers are still worth showing without a key.
            ->assertSee('41%');

        Http::assertNothingSent();
    }

    public function test_the_key_stored_in_settings_is_the_one_used(): void
    {
        app(SettingRepository::class)->set('weather_api_key', 'user-key');

        Http::fake(['*' => Http::response(['cod' => 200, 'weather' => [['main' => 'Clear']]])]);

        app(WeatherService::class)->byCoordinates(35.69, 51.42);

        // setUp pins the environment key to "test-key", so this also proves
        // the stored preference outranks it.
        Http::assertSent(fn ($request) => str_contains($request->url(), 'appid=user-key'));
    }

    public function test_an_empty_stored_key_falls_back_to_the_environment(): void
    {
        app(SettingRepository::class)->set('weather_api_key', '   ');

        Http::fake(['*' => Http::response(['cod' => 200, 'weather' => [['main' => 'Clear']]])]);

        app(WeatherService::class)->byCoordinates(35.69, 51.42);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'appid=test-key'));
    }
}
