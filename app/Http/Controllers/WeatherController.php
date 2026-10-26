<?php

namespace App\Http\Controllers;

use App\Contracts\SettingRepository;
use App\Contracts\WeatherService;
use App\Exceptions\WeatherException;
use App\Models\City;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Weather dashboard plus management of saved cities.
 */
class WeatherController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected WeatherService $weather,
        protected SettingRepository $settings,
    ) {}

    /**
     * Show the weather dashboard.
     *
     * Supports two modes:
     *  - "?city=Name" renders the saved city matching that name.
     *  - Without parameters the home town (or first saved city) is shown.
     *
     * Live device coordinates are handled by locate() instead.
     */
    public function index(Request $request): View
    {
        $city = $this->resolveCity($request->query('city'));

        $weather = null;
        $error = null;
        $fromSnapshot = false;

        if ($city !== null) {
            try {
                $weather = $this->fetchFor($city);
            } catch (WeatherException $exception) {
                // A saved city keeps its last reading, and every reading column
                // is required, so there is always something to show.
                $weather = $city->snapshotPayload();
                $fromSnapshot = true;
                $error = $exception->getMessage();
            }
        }

        return view('weather.index', [
            'city' => $city,
            'weather' => $weather,
            'error' => $error,
            'fromSnapshot' => $fromSnapshot,
            'needsKey' => ! $this->weather->isConfigured(),
            'isLiveLocation' => false,
        ]);
    }

    /**
     * Fetch a city's current weather.
     *
     * Saved cities carry coordinates and those address the station exactly,
     * while a name lookup resolves to whatever the API thinks you meant; the
     * name is only the fallback for a row saved without them.
     *
     * @throws WeatherException
     */
    protected function fetchFor(City $city): array
    {
        [$lat, $lon] = [data_get($city->coordinates, 'lat'), data_get($city->coordinates, 'lon')];

        return is_numeric($lat) && is_numeric($lon)
            ? $this->weather->byCoordinates((float) $lat, (float) $lon)
            : $this->weather->byCity($city->name);
    }

    /**
     * Render the dashboard from raw geographic coordinates without saving
     * anything; used by the "use my location" button on the dashboard.
     */
    public function locate(Request $request): View
    {
        $latitude = (float) $request->query('lat');
        $longitude = (float) $request->query('lon');

        try {
            $weather = $this->weather->byCoordinates($latitude, $longitude);
        } catch (WeatherException $exception) {
            // Nothing is stored for a one-off coordinate, so there is no
            // snapshot to fall back on here.
            return view('weather.index', [
                'city' => null,
                'weather' => null,
                'error' => $exception->getMessage(),
                'fromSnapshot' => false,
                'needsKey' => ! $this->weather->isConfigured(),
                'isLiveLocation' => true,
            ]);
        }

        return view('weather.index', [
            'city' => null,
            'weather' => $weather,
            'error' => null,
            'fromSnapshot' => false,
            'needsKey' => false,
            'isLiveLocation' => true,
        ]);
    }

    /**
     * List saved cities and handle free-text lookups against the API.
     */
    public function cities(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $cities = filled($search)
            ? City::whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])->orderBy('name')->get()
            : $this->citiesWithHomeFirst();

        $remote = null;
        $error = null;

        // Nothing stored locally matches: offer a fresh lookup result instead.
        if (filled($search) && $cities->isEmpty()) {
            try {
                $remote = $this->weather->byCity($search);
            } catch (WeatherException $exception) {
                $error = $exception->getMessage();
            }
        }

        return view('weather.cities', [
            'cities' => $cities,
            'search' => $search,
            'remote' => $remote,
            'error' => $error,
        ]);
    }

    /**
     * Persist a city discovered through the API lookup.
     */
    public function storeCity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'weather' => ['required', 'json'],
        ]);

        $payload = json_decode($validated['weather'], true);

        // Guard against malformed API payloads before touching the database.
        foreach (['name', 'coord', 'weather.0', 'main', 'sys', 'wind'] as $pointer) {
            if (data_get($payload, $pointer) === null) {
                return back()->with('error', 'The weather service returned incomplete data.');
            }
        }

        $city = City::create([
            'name' => $payload['name'],
            'main' => $payload['weather'][0]['main'],
            'icon' => $payload['weather'][0]['icon'] ?? '',
            'description' => $payload['weather'][0]['description'] ?? '',
            'coordinates' => $payload['coord'],
            'temperature' => $payload['main']['temp'],
            'country_code' => $payload['sys']['country'],
            'feels_like' => $payload['main']['feels_like'],
            'humidity' => $payload['main']['humidity'],
            'pressure' => $payload['main']['pressure'],
            'wind_speed' => $payload['wind']['speed'] ?? 0,
            'wind_direction' => $payload['wind']['deg'] ?? 0,
            'sunrise' => isset($payload['sys']['sunrise']) ? date('Y-m-d H:i:s', (int) $payload['sys']['sunrise']) : now(),
            'sunset' => isset($payload['sys']['sunset']) ? date('Y-m-d H:i:s', (int) $payload['sys']['sunset']) : now(),
            'sea_level' => $payload['main']['sea_level'] ?? $payload['main']['pressure'],
        ]);

        return redirect()
            ->route('weather.cities.index')
            ->with('success', "{$city->name} added.");
    }

    /**
     * Toggle the home-town flag for the given city.
     */
    public function setHomeTown(City $city): RedirectResponse
    {
        if ($city->isHomeTown()) {
            $this->settings->forget('home-town');

            return back()->with('info', "{$city->name} is no longer your home town.");
        }

        $this->settings->set('home-town', $city->name);

        return back()->with('success', "{$city->name} is now your home town.");
    }

    /**
     * Refresh a city's stored weather snapshot.
     */
    public function refresh(City $city): RedirectResponse
    {
        try {
            $this->applySnapshot($city, $this->fetchFor($city));
            $city->save();
        } catch (WeatherException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('weather.index', ['city' => $city->name])
            ->with('success', "{$city->name} updated.");
    }

    /**
     * Soft-delete the given city so it can be restored from the bin.
     */
    public function destroy(City $city): RedirectResponse
    {
        DB::transaction(function () use ($city): void {
            if ($city->isHomeTown()) {
                $this->settings->forget('home-town');
            }

            $city->delete();
        });

        return redirect()
            ->route('weather.cities.index')
            ->with('success', "{$city->name} moved to the recycle bin.");
    }

    /**
     * Resolve which saved city the dashboard should show.
     */
    protected function resolveCity(?string $name): ?City
    {
        if (filled($name)) {
            return City::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])->first();
        }

        $homeTown = $this->settings->get('home-town');

        return ($homeTown ? $this->findByName($homeTown) : null)
            ?? City::orderBy('id')->first();
    }

    /**
     * All saved cities ordered with the home town first.
     *
     * @return \Illuminate\Support\Collection<int, City>
     */
    protected function citiesWithHomeFirst(): \Illuminate\Support\Collection
    {
        return City::query()
            ->orderByRaw('CASE WHEN name = ? THEN 0 ELSE 1 END', [(string) $this->settings->get('home-town')])
            ->orderBy('name')
            ->get()
            ->values();
    }

    /**
     * Case-insensitive lookup of a saved city by name.
     */
    protected function findByName(string $name): ?City
    {
        return City::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])->first();
    }

    /**
     * Write an API payload onto a city model (call save() to persist).
     *
     * Missing keys fall back to the values already stored on the model so a
     * partial response can never blank out existing snapshot data.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function applySnapshot(City $city, array $payload): void
    {
        $city->fill([
            'name' => $payload['name'] ?? $city->name,
            'main' => $payload['weather'][0]['main'] ?? $city->main ?? 'Unknown',
            'icon' => $payload['weather'][0]['icon'] ?? $city->icon ?? '',
            'description' => $payload['weather'][0]['description'] ?? $city->description ?? '',
            'coordinates' => $payload['coord'] ?? $city->coordinates,
            'temperature' => $payload['main']['temp'] ?? $city->temperature,
            'country_code' => $payload['sys']['country'] ?? $city->country_code ?? '',
            'feels_like' => $payload['main']['feels_like'] ?? $city->feels_like,
            'humidity' => $payload['main']['humidity'] ?? $city->humidity,
            'pressure' => $payload['main']['pressure'] ?? $city->pressure,
            'wind_speed' => $payload['wind']['speed'] ?? $city->wind_speed ?? 0,
            'wind_direction' => $payload['wind']['deg'] ?? $city->wind_direction ?? 0,
            'sunrise' => isset($payload['sys']['sunrise']) ? date('Y-m-d H:i:s', (int) $payload['sys']['sunrise']) : $city->sunrise,
            'sunset' => isset($payload['sys']['sunset']) ? date('Y-m-d H:i:s', (int) $payload['sys']['sunset']) : $city->sunset,
            'sea_level' => $payload['main']['sea_level'] ?? $city->sea_level ?? 0,
        ]);
    }
}
