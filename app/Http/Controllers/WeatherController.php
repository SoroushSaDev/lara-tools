<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Setting;
use App\Services\OpenWeatherService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class WeatherController extends Controller
{
    protected OpenWeatherService $weatherService;

    /**
     * @param OpenWeatherService $weatherService
     */
    public function __construct(OpenWeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * @return Factory|View|Application|\Illuminate\View\View|object
     */
    public function index(Request $request)
    {
        $home = Setting::where('key', 'home-town')?->first() ?? null;
        $city = City::firstWhere('name', ($request->has('city') ? $request['city'] : $home?->value ?? null)) ?? null;
        $weatherService = new OpenWeatherService();
        $weather = $weatherService->getWeatherByCity($city?->name ?? '') ?? null;

        return view('weather.index', compact('city', 'weather'));
    }

    /**
     * @param City $city
     * @return RedirectResponse
     */
    public function data(City $city)
    {
        $weatherService = new OpenWeatherService();
        $weather = $weatherService->getWeatherByCity($city->name);

        return redirect()->route('weather.index', ['city' => $city->name, 'weather' => $weather]);
    }

    /**
     * @param $city
     * @return JsonResponse
     */
    public function byCity($city)
    {
        $weather = $this->weatherService->getWeatherByCity($city);
        return response()->json($weather);
    }

    /**
     * @param $lat
     * @param $lon
     * @return JsonResponse
     */
    public function byCoordinates($lat, $lon)
    {
        $weather = $this->weatherService->getWeatherByCoordinates($lat, $lon);
        return response()->json($weather);
    }

    /**
     * @param Request $request
     * @return Factory|View|Application|RedirectResponse|\Illuminate\View\View|object
     * @throws Throwable
     */
    public function cities(Request $request)
    {
        if ($request->isMethod('get')) {
            if ($request->has('search') && $request->search != '') {
                $search = __Title($request['search']);
                $cities = City::where('name', 'like', '%' . $search . '%')->get();

                if (!count($cities)) {
                    $weatherService = new OpenWeatherService();
                    $weather = $weatherService->getWeatherByCity($request->search);
                    if ($weather['cod'] == 404)
                        $weather = null;
                } else {
                    $weather = null;
                }
            } else {
                $search = null;
                $weather = null;
                $cities = City::all()->sortByDesc(function ($city) {
                    $setting = Setting::where('key', 'home-town')->first();
                    return $setting->value == $city->name;
                });
            }

            return view('weather.cities', compact('cities', 'weather', 'search'));
        } else {
            $weather = json_decode($request['weather'], true);
            DB::beginTransaction();
            try {
                City::create([
                    'name' => $weather['name'],
                    'main' => $weather['weather'][0]['main'],
                    'icon' => $weather['weather'][0]['icon'],
                    'description' => $weather['weather'][0]['description'],
                    'coordinates' => json_encode($weather['coord']),
                    'temperature' => $weather['main']['temp'],
                    'country_code' => $weather['sys']['country'],
                    'feels_like' => $weather['main']['feels_like'],
                    'humidity' => $weather['main']['humidity'],
                    'pressure' => $weather['main']['pressure'],
                    'wind_speed' => $weather['wind']['speed'],
                    'wind_direction' => $weather['wind']['deg'],
                    'sunrise' => $weather['sys']['sunrise'],
                    'sunset' => $weather['sys']['sunset'],
                    'sea_level' => $weather['main']['sea_level'],
                ]);
                DB::commit();
                return redirect()->route('weather.cities')->with('success', 'City added successfully');
            } catch (\Exception $exception) {
                DB::rollBack();
                return redirect()->back()->with('error', $exception);
            }
        }
    }

    /**
     * @param City $city
     * @return RedirectResponse
     */
    public function homeTown(City $city)
    {
        Setting::where('key', 'home-town')->update(['value' => $city->name ?? '']);
        return redirect()->back()->with('success', 'HomeCity set successfully');
    }

    /**
     * @param City $city
     * @return RedirectResponse
     */
    public function update(City $city)
    {
        $weatherService = new OpenWeatherService();
        $weather = $weatherService->getWeatherByCity($city?->name ?? '') ?? null;

        $city->update([
            'name' => $weather['name'],
            'main' => $weather['weather'][0]['main'],
            'icon' => $weather['weather'][0]['icon'],
            'description' => $weather['weather'][0]['description'],
            'coordinates' => json_encode($weather['coord']),
            'temperature' => $weather['main']['temp'],
            'country_code' => $weather['sys']['country'],
            'feels_like' => $weather['main']['feels_like'],
            'humidity' => $weather['main']['humidity'],
            'pressure' => $weather['main']['pressure'],
            'wind_speed' => $weather['wind']['speed'],
            'wind_direction' => $weather['wind']['deg'],
            'sunrise' => $weather['sys']['sunrise'],
            'sunset' => $weather['sys']['sunset'],
            'sea_level' => $weather['main']['sea_level'],
        ]);

        return redirect()->route('weather.index', ['city' => $city->name, 'weather' => $weather]);
    }

    public function delete(City $city)
    {
        $city->delete();
        return redirect()->back()->with('success', 'City deleted successfully');
    }
}
