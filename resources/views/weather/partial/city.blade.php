<div id="data" class="w-full">
    @if(isset($weather['main']))
        <div class="flex max-sm:flex-col items-center max-sm:space-y-5 sm:space-x-10 max-sm:w-full">
            <div class="flex flex-col items-center max-sm:space-y-3 sm:space-y-6">
                <h1 class="max-sm:text-7xl sm:text-9xl font-bold">
                    {{ $weather['main']['temp'] }}°C
                </h1>
                <div class="flex justify-between items-center w-full">
                    <button type="button" class="hover:text-gray-400 cursor-pointer max-sm:text-xl sm:text-3xl"
                            onclick="getWeather();">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <h2 class="max-sm:text-4xl sm:text-6xl">
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
</div>
