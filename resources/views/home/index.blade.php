@extends('layouts.main')
@section('title', 'Home')
@section('description', 'Pick what you need')
@section('content')
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-6 mt-5 lg:mt-0">
        <a href="{{ route('notes.index') }}"
           class="flex flex-col justify-center items-center space-y-5 lg:space-y-10 font-semibold text-lg lg:text-2xl py-10 px-5 lg:p-20 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl">
            <i class="bi bi-sticky-fill text-5xl lg:text-6xl"></i>
            <span>
                Notes
            </span>
        </a>
        <a href="{{ route('todos.index') }}"
           class="flex flex-col justify-center items-center space-y-5 lg:space-y-10 font-semibold text-lg lg:text-2xl py-10 px-5 lg:p-20 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl">
            <i class="bi bi-list-task text-5xl lg:text-6xl"></i>
            <span>
                Todo List
            </span>
        </a>
        <a href="{{ route('calculator') }}"
           class="flex flex-col justify-center items-center space-y-5 lg:space-y-10 font-semibold text-lg lg:text-2xl py-10 px-5 lg:p-20 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl">
            <i class="bi bi-calculator-fill text-5xl lg:text-6xl"></i>
            <span>
                Calculator
            </span>
        </a>
        <a href="{{ route('music.index') }}"
           class="flex flex-col justify-center items-center space-y-5 lg:space-y-10 font-semibold text-lg lg:text-2xl py-10 px-5 lg:p-20 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl">
            <i class="bi bi-music-player-fill text-5xl lg:text-6xl"></i>
            <span>
                Music Player
            </span>
        </a>
        <a href="{{ route('home') }}"
           class="flex flex-col justify-center items-center space-y-5 lg:space-y-10 font-semibold text-lg lg:text-2xl py-10 px-5 lg:p-20 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl">
            <i class="bi bi-cloud-sun-fill text-5xl lg:text-6xl"></i>
            <span>
                Weather
            </span>
        </a>
    </div>
@endsection
