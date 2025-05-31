@extends('layouts.main')
@section('header-s')
    <a href="{{ route('calendar.index') }}?date={{ $date }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="invisible lg:visible">
            Calendar
        </span>
    </a>
@endsection
@section('title', 'Add Event')
@section('header-e')
    <button type="button"
        class="flex items-center space-x-2 text-xl hover:text-green-500 hover:cursor-pointer"
        onClick="document.querySelector('#event').submit()">
        <i class="bi bi-check2-circle"></i>
        <span class="hidden lg:block">
            Save
        </span>
    </button>
@endsection
@section('content')
    <form id="event" class="grid max-sm:grid-cols-1 sm:grid-cols-3 gap-2 w-full" method="POST" action="{{ route('calendar.store') }}">
        @CSRF
        <input type="text" name="title"
            class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 border-none placeholder-gray-300 rounded-md p-3"
            placeholder="Title"/>
        <input type="date" name="date"
            class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 border-none placeholder-gray-300 rounded-md p-3"
            placeholder="Date" value="{{ $date->format('Y-m-d') }}"/>
        <input type="time" name="time"
            class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 border-none placeholder-gray-300 rounded-md p-3"
            placeholder="Time" value="{{ $date->format('H:i') }}"/>
        <textarea name="description"
            class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 border-none placeholder-gray-300 rounded-md resize-none p-3"
            placeholder="Description (Optional)"></textarea>
        <textarea name="address"
            class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 border-none placeholder-gray-300 rounded-md resize-none p-3"
            placeholder="Address (Optional)"></textarea>
    </form>
@endsection
