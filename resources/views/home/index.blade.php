@extends('layouts.main')

@php
    use Carbon\Carbon;

    $hour = now()->format('G');
    $greeting = match (true) {
        $hour < 5 => 'Good night',
        $hour < 12 => 'Good morning',
        $hour < 18 => 'Good afternoon',
        default => 'Good evening',
    };
@endphp

@section('title', 'Dashboard')

@section('content')
    <x-page-header>
        <x-slot:title>{{ $greeting }}</x-slot:title>
        <x-slot:subtitle>What would you like to do today?</x-slot:subtitle>

        <x-slot:actions>
            <a href="{{ route('notes.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Quick note</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Live counters --}}
    <div class="mb-6 grid grid-cols-2 gap-3 animate-fade-up sm:grid-cols-4 sm:gap-4" style="animation-delay: 60ms">
        @include('home.partial.stat', ['icon' => 'bi-journal-text', 'value' => $stats['notes'], 'label' => str('note')->plural($stats['notes']), 'gradient' => 'from-amber-400 to-orange-500'])
        @include('home.partial.stat', ['icon' => 'bi-inbox', 'value' => $stats['openItems'], 'label' => 'open '.str('item')->plural($stats['openItems']), 'gradient' => 'from-emerald-400 to-teal-500'])
        @include('home.partial.stat', ['icon' => 'bi-calendar-event', 'value' => $stats['events'], 'label' => 'upcoming '.str('event')->plural($stats['events']), 'gradient' => 'from-violet-500 to-purple-600'])
        @include('home.partial.stat', ['icon' => 'bi-music-note-beamed', 'value' => $stats['tracks'], 'label' => str('track')->plural($stats['tracks']), 'gradient' => 'from-pink-400 to-fuchsia-600'])
    </div>

    {{-- Tool launcher grid --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-3 xl:grid-cols-4">
        @include('home.partial.item', [
            'route' => route('notes.index'),
            'icon' => 'bi-journal-text',
            'iconFill' => 'bi-journal-text',
            'label' => 'Notes',
            'gradient' => 'from-amber-400 to-orange-500',
            'shadow' => 'shadow-orange-500/30',
            'delay' => 100,
        ])
        @include('home.partial.item', [
            'route' => route('todos.index'),
            'icon' => 'bi-list-check',
            'iconFill' => 'bi-list-check',
            'label' => 'Todo Lists',
            'gradient' => 'from-emerald-400 to-teal-600',
            'shadow' => 'shadow-emerald-500/30',
            'delay' => 160,
        ])
        @include('home.partial.item', [
            'route' => route('calendar.index'),
            'icon' => 'bi-calendar-date',
            'iconFill' => 'bi-calendar-date-fill',
            'label' => 'Calendar',
            'gradient' => 'from-violet-500 to-purple-600',
            'shadow' => 'shadow-violet-500/30',
            'delay' => 220,
        ])
        @include('home.partial.item', [
            'route' => route('music.index'),
            'icon' => 'bi-music-player',
            'iconFill' => 'bi-music-player-fill',
            'label' => 'Music Player',
            'gradient' => 'from-pink-400 to-fuchsia-600',
            'shadow' => 'shadow-fuchsia-500/30',
            'delay' => 280,
        ])
        @include('home.partial.item', [
            'route' => route('weather.index'),
            'icon' => 'bi-cloud-sun',
            'iconFill' => 'bi-cloud-sun-fill',
            'label' => 'Weather',
            'gradient' => 'from-sky-400 to-blue-600',
            'shadow' => 'shadow-sky-500/30',
            'delay' => 340,
        ])
        @include('home.partial.item', [
            'route' => route('calculator'),
            'icon' => 'bi-calculator',
            'iconFill' => 'bi-calculator-fill',
            'label' => 'Calculator',
            'gradient' => 'from-lime-400 to-green-600',
            'shadow' => 'shadow-lime-500/30',
            'delay' => 400,
        ])
        @include('home.partial.item', [
            'route' => route('clock'),
            'icon' => 'bi-clock',
            'iconFill' => 'bi-clock-fill',
            'label' => 'Clock',
            'gradient' => 'from-blue-400 to-indigo-600',
            'shadow' => 'shadow-indigo-500/30',
            'delay' => 400,
        ])
        @include('home.partial.item', [
            'route' => route('converter'),
            'icon' => 'bi-calculator',
            'iconFill' => 'bi-calculator-fill',
            'label' => 'Unit Converter',
            'gradient' => 'from-cyan-400 to-sky-600',
            'shadow' => 'shadow-cyan-500/30',
            'delay' => 460,
        ])
    </div>
@endsection
