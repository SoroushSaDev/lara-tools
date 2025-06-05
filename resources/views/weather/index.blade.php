@extends('layouts.main')
@section('header-s')
    <a href="{{ route('home') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="hidden lg:block">
            Home
        </span>
    </a>
    @if(!$city->isHomeTown())
        <a href="{{ route('weather.index') }}" class="flex items-center space-x-2 text-xl hover:text-yellow-300">
            <i class="bi bi-star-fill"></i>
            <span class="hidden lg:block">
                Home Town
            </span>
        </a>
    @endif
@endsection
@section('title', 'Weather')
@section('header-e')
    <a href="{{ route('weather.update', $city) }}" class="flex items-center space-x-2 text-xl hover:text-blue-300">
        <i class="bi bi-arrow-clockwise"></i>
        <span class="hidden lg:block">
            Update
        </span>
    </a>
    <a href="{{ route('weather.cities') }}" class="flex items-center space-x-2 text-xl hover:text-yellow-300">
        <i class="bi bi-pencil-fill"></i>
        <span class="hidden lg:block">
            Manage Cities
        </span>
    </a>
@endsection
@section('content')
    @if(!is_null($city))
        @if(isset($weather['main']))
            <div class="flex flex-col items-center max-sm:space-y-5 sm:space-y-10 max-sm:w-full">
                <div class="flex max-sm:flex-col items-center max-sm:space-y-5 sm:space-x-10 w-full">
                    <div class="flex flex-col items-center max-sm:space-y-3 sm:space-y-6">
                        <h1 id="temp" class="max-sm:text-7xl sm:text-9xl font-bold">
                            {{ $weather['main']['temp'] }}°C
                        </h1>
                        <div class="flex justify-between items-center w-full">
                            <a href="{{ $city->getLast() != 0 ? route('weather.data', $city->getLast()) : '#' }}"
                               class="hover:text-gray-400 max-sm:text-xl sm:text-3xl">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                            <h2 id="city" class="max-sm:text-4xl sm:text-6xl">
                                {{ $weather['name'] }}
                            </h2>
                            <a href="{{ $city->getNext() != 0 ? route('weather.data', $city->getNext()) : '#' }}"
                               class="hover:text-gray-400 max-sm:text-xl sm:text-3xl">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                        <h3 class="max-sm:text-xl sm:text-3xl font-semibold">
                            {{ $weather['weather'][0]['main'] }}
                        </h3>
                    </div>
                    <div
                        class="flex flex-col items-center max-sm:space-y-3 sm:space-y-6 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg max-sm:w-full sm:w-100 max-sm:p-3 sm:p-6 max-sm:text-lg sm:text-2xl">
                        @include('weather.partial.property', ['label' => 'Country code', 'value' => $weather['sys']['country']])
                        @include('weather.partial.property', ['label' => 'Feels like', 'value' => $weather['main']['feels_like'], 'unit' => '°C'])
                        @include('weather.partial.property', ['label' => 'Humidity', 'value' => $weather['main']['humidity'], 'unit' => '%'])
                        @include('weather.partial.property', ['label' => 'Pressure', 'value' => $weather['main']['pressure'], 'unit' => ' hPa'])
                        @include('weather.partial.property', ['label' => 'Wind Speed', 'value' => $weather['wind']['speed'], 'unit' => ' m/s'])
                        @include('weather.partial.property', ['label' => 'Wind Direction', 'value' => $weather['wind']['deg'], 'unit' => '°'])
                    </div>
                </div>
                <ul class="space-y-3 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg w-full p-3 max-sm:text-sm sm:text-md font-semibold">
                    @if($city->isHomeTown())
                        <li class="flex items-center space-x-3">
                            <i class="bi bi-star-fill"></i>
                            <p>
                                Showing Home Town
                            </p>
                        </li>
                    @endif
                    <li class="flex items-center space-x-3">
                        <i class="bi bi-info-circle-fill"></i>
                        <p>
                            Updated at {{ $city->updated_at->timezone('Asia/Tehran')->format('l, j F Y | H:i:s') }}
                        </p>
                    </li>
                </ul>
            </div>
        @else
            <p>
                <i class="bi bi-info-circle-fill me-1"></i>
                Weather data not available
            </p>
        @endif
    @else
        <p>
            <i class="bi bi-info-circle-fill me-1"></i>
            Add a city first
        </p>
    @endif
@endsection
@push('script')
    <script>
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    console.log(`Latitude: ${latitude}, Longitude: ${longitude}`);

                    // You can use these values as needed
                    // doSomethingWithLocation(latitude, longitude);
                },
                (error) => {
                    console.error("Error getting location:", error.message);
                    // Handle errors appropriately
                }
            );
        } else {
            console.log("Geolocation is not supported by this browser.");
        }
    </script>
@endpush
