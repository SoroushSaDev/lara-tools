<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme-pref="{{ $appearance }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">

    <title>@yield('title', 'LaraTools') · LaraTools</title>

    <!-- Apply the saved theme before first paint to avoid a flash.
         Client-side choices (localStorage) win over the server-rendered
         preference so a local toggle never gets reverted on reload. -->
    <script>
        (function () {
            var pref = localStorage.getItem('lara-tools.theme')
                || document.documentElement.dataset.themePref
                || 'dark';
            var dark = pref === 'dark'
                || (pref === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('style')
</head>
<body class="app-shell min-h-screen font-sans font-semibold text-slate-800 antialiased select-none dark:text-slate-100 {{ request()->routeIs('music.*') ? 'on-music-page' : '' }}"
      data-music-shuffle-default="{{ $musicShuffleDefault ? '1' : '0' }}"
      data-confirm-deletes="{{ $confirmDeletes ? '1' : '0' }}">
    <!-- Decorative floating background orbs -->
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 right-[8%] size-[26rem] rounded-full bg-violet-500/20 blur-3xl animate-float-a"></div>
        <div class="absolute bottom-[-8rem] left-[-6rem] size-[24rem] rounded-full bg-fuchsia-500/15 blur-3xl animate-float-b"></div>
    </div>

    <div class="flex min-h-screen">
        <!-- Desktop sidebar -->
        @include('layouts.partial.sidebar')

        <main class="flex min-h-screen min-w-0 flex-1 flex-col lg:pl-72">
            <!-- Mobile top bar -->
            @include('layouts.partial.top-bar')

            <!-- Page content: modules own the full window, the padding is the gutter -->
            <div class="w-full flex-1 px-4 pt-6 pb-36 sm:px-8 lg:px-10 lg:pb-12 2xl:px-16">
                @yield('content')
            </div>

            @include('layouts.partial.footer')
        </main>
    </div>

    <!-- Mobile bottom navigation -->
    @include('layouts.partial.bottom-nav')

    <!-- Persistent music player (mini everywhere, full deck on Music) -->
    @include('layouts.partial.player')

    <!-- Global overlays -->
    @include('components.toast-stack')
    @include('components.confirm-dialog')

    @stack('script')
</body>
</html>
