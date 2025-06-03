@extends('layouts.main')
@section('title', 'Home')
@section('description', 'Pick what you need')
@section('content')
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 mt-5 lg:mt-0">
        @include('home.partial.item', ['route' => route('notes.index'), 'icon' => 'sticky-fill', 'label' => 'Notes'])
        @include('home.partial.item', ['route' => route('todos.index'), 'icon' => 'list-task', 'label' => 'Todo List'])
        @include('home.partial.item', ['route' => route('calculator'), 'icon' => 'calculator-fill', 'label' => 'Calculator'])
        @include('home.partial.item', ['route' => route('music.index'), 'icon' => 'music-player-fill', 'label' => 'Music Player'])
        @include('home.partial.item', ['route' => route('calendar.index'), 'icon' => 'calendar-date-fill', 'label' => 'Calendar'])
        @include('home.partial.item', ['route' => route('weather.index'), 'icon' => 'cloud-sun-fill', 'label' => 'Weather'])
    </div>
@endsection
