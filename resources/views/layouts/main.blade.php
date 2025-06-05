<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @yield('title')
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="{{ asset('assets/fonts/fonts.bunny.net.css') }}" rel="stylesheet"/>
    <link rel="preload" href="{{ asset('fonts/Outfit-VariableFont_wght.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Quicksand-VariableFont_wght.ttf') }}" as="font" type="font/ttf"
          crossorigin>

    <!-- Styles / Scripts -->
    <link href="{{ asset('assets/flowbite/flowbite.min.css') }}" rel="stylesheet"/>
    @include('layouts.partial.styles')
    @stack('style')
</head>
<body
    class="bg-conic/decreasing from-violet-900 via-lime-900 to-violet-900 text-white flex p-3 lg:p-5 items-center lg:justify-center min-h-screen overflow-y-auto select-none flex-col scroll-smooth pt-20 pb-40">
<div class="fixed flex flex-col items-center !space-y-6 top-0 w-full p-3 z-30 backdrop-blur-3xl">
    <div class="grid grid-cols-5 gap-3 lg:gap-6 w-full sm:mb-0">
        <div class="flex justify-start items-center space-x-3 lg:space-x-6">
            @yield('header-s')
        </div>
        <h1 class="col-span-3 text-3xl lg:text-5xl font-bold text-center">
            @yield('title')
        </h1>
        <div class="flex justify-end items-center space-x-3 lg:space-x-6">
            @yield('header-e')
        </div>
    </div>
    <div class="max-sm:w-full sm:w-[600px]">
        @yield('top-bar')
    </div>
</div>
<div
    class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0 max-sm:pt-10 sm:pt-50">
    @yield('content')
</div>
<div class="fixed bottom-0 left-0 w-full flex flex-col">
    @yield('bottom-bar')
    @include('layouts.partial.footer')
</div>
@include('layouts.partial.scripts')
@stack('script')
</body>
</html>
