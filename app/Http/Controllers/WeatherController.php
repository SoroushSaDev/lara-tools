<?php

namespace App\Http\Controllers;

use App\Services\OpenWeatherService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $city = $request->has('city') ? $request->city : 'Qazvin';
        $weatherService = new OpenWeatherService();
        $weather = $weatherService->getWeatherByCity($city);

        return view('weather.index', compact('city', 'weather'));
    }

    public function data(Request $request)
    {
        $city = $request->has('city') ? $request->city : 'Qazvin';
        $weatherService = new OpenWeatherService();
        $weather = $weatherService->getWeatherByCity($city);

        return response()->json([
            'data' => $weather,
        ], 200);
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
}
