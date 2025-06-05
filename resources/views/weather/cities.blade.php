@extends('layouts.main')
@section('header-s')
    <a href="{{ route('weather.index') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="invisible lg:visible">
            Weather
        </span>
    </a>
@endsection
@section('title', 'Manage Cities')
@section('header-e')

@endsection
@section('top-bar')
    <form method="get" action="{{ route('weather.cities') }}"
          class="flex items-center space-x-3 w-full font-semibold">
        <input type="text" name="search" value="{{ $search }}"
               class="backdrop-blur-3xl bg-white/30 dark:bg-black/30 border-none placeholder-gray-400 rounded-full py-2 px-5 w-full"
               placeholder="Search city name">
        <button type="submit"
                class="flex items-center sm:space-x-3 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-full py-2 px-3 cursor-pointer hover:shadow-2xl hover:text-blue-500">
            <i class="bi bi-search"></i>
            <span class="max-sm:hidden">
                Search
            </span>
        </button>
    </form>
@endsection
@section('content')
    <ul class="grid max-sm:grid-cols-1 sm:grid-cols-3 gap-2 w-full max-sm:mt-5">
        @if(count($cities))
            @foreach($cities as $city)
                <li class="flex items-center space-x-3 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg w-full max-sm:p-3 sm:p-6">
                    <a href="{{ route('weather.data', $city) }}" class="grid grid-cols-3 gap-10 w-full">
                        <span class="font-bold max-sm:text-4xl sm:text-6xl">
                            {{ round($city->temperature) }}°C
                        </span>
                        <div class="flex flex-col col-span-2">
                            <span class="font-semibold max-sm:text-lg sm:text-3xl">
                                {{ $city->name }}
                            </span>
                            <span class="max-sm:text-xs sm:text-md font-semibold">
                                {{ $city->main }}
                            </span>
                        </div>
                    </a>
                    <div class="flex justify-end items-center space-x-3">
                        @csrf
                        <a href="{{ route('weather.set', $city) }}"
                           class="backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-full py-2 px-3 w-min hover:shadow-2xl hover:text-yellow-500">
                            <i class="bi bi-star{{ $city->isHomeTown() ? '-fill' : '' }}"></i>
                        </a>
                        <a href="{{ route('weather.delete', $city) }}"
                           class="backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-full py-2 px-3 w-min hover:shadow-2xl hover:text-red-500">
                            <i class="bi bi-trash3-fill"></i>
                        </a>
                    </div>
                </li>
            @endforeach
        @elseif(!is_null($weather))
            <li class="sm:col-start-2 grid grid-cols-3 gap-6 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg w-full max-sm:p-3 sm:p-6">
                    <span class="font-bold max-sm:text-5xl sm:text-6xl">
                        {{ round($weather['main']['temp']) }}°C
                    </span>
                <div class="flex flex-col">
                        <span class="font-semibold max-sm:text-xl sm:text-3xl">
                            {{ $weather['name'] }}
                        </span>
                    <span class="maz-sm:text-sm sm:text-lg">
                            {{ $weather['weather'][0]['main'] }}
                        </span>
                </div>
                <form method="post" action="{{ route('weather.cities') }}" class="flex justify-end items-center">
                    @csrf
                    <input type="hidden" name="weather" value="{{ json_encode($weather) }}">
                    <button type="submit"
                            class="flex items-center sm:space-x-3 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-full py-2 max-sm:px-3 sm:ps-2 sm:pe-5 w-min cursor-pointer hover:shadow-2xl">
                        <i class="bi bi-plus-lg sm:bg-white sm:py-2 sm:px-3 sm:rounded-full sm:text-black"></i>
                        <span class="max-sm:hidden sm:text-2xl font-semibold">
                                Add
                            </span>
                    </button>
                </form>
            </li>
        @else
            <li class="text-center sm:col-start-2">
                <i class="bi bi-info-circle-fill"></i>
                <span class="ms-1">
                    No result
                </span>
            </li>
        @endif
    </ul>
@endsection
