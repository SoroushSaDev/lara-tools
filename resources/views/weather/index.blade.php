@extends('layouts.main')
@section('header-s')
    <a href="{{ route('home') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="invisible lg:visible">
            Home
        </span>
    </a>
@endsection
@section('title', 'Weather')
@section('header-e')
    <a href="{{ route('music.pick') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-plus-lg"></i>
        <span class="hidden lg:block">
            Add City
        </span>
    </a>
@endsection
@section('content')
    @if(isset($weather['main']))
        <div class="flex max-sm:flex-col items-center max-sm:space-y-5 sm:space-x-10 max-sm:w-full">
            <div class="flex flex-col items-center max-sm:space-y-3 sm:space-y-6">
                <h1 id="temp" class="max-sm:text-7xl sm:text-9xl font-bold">
                    {{ $weather['main']['temp'] }}°C
                </h1>
                <div class="flex justify-between items-center w-full">
                    <button type="button" class="hover:text-gray-400 cursor-pointer max-sm:text-xl sm:text-3xl"
                            onclick="getWeather();">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <h2 id="city" class="max-sm:text-4xl sm:text-6xl">
                        {{ $weather['name'] }}
                    </h2>
                    <button type="button" class="hover:text-gray-400 cursor-pointer max-sm:text-xl sm:text-3xl"
                            onclick="getWeather();">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <h3 class="max-sm:text-xl sm:text-3xl font-semibold">
                    {{ $weather['weather'][0]['description'] }}
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
    @else
        <p>
            Weather data not available
        </p>
    @endif
@endsection
@push('script')
    <script>
        async function getWeather() {
            const response = await fetch('{{ route('weather.data', ['city' => 'Tehran']) }}').then(res => res.json());
            console.log(response.data);

            document.getElementById('city').innerText = response.data.name;
        }
    </script>
@endpush
