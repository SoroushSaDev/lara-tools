@extends('layouts.main')

@php
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

    {{-- Sections arrive already filtered and ordered by HomeController --}}
    @unless ($allHidden)
        <div class="space-y-6">
            @foreach ($sections as $section)
                @php($tiles = $section['tiles'])

                @switch($section['key'])
                    @case('stats')
                        <div class="grid grid-cols-2 gap-3 animate-fade-up sm:gap-4 {{ count($tiles) === 1 ? 'sm:grid-cols-2' : 'sm:grid-cols-4' }}"
                             style="animation-delay: 60ms">
                            @foreach ($tiles as $stat)
                                @include('home.partial.stat', $stat)
                            @endforeach
                        </div>
                    @break

                    @case('shortcuts')
                        <div class="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-3 xl:grid-cols-4">
                            @foreach ($tiles as $shortcut)
                                @include('home.partial.item', [...$shortcut, 'delay' => 100 + $loop->index * 60])
                            @endforeach
                        </div>
                    @break

                    @case('widgets')
                        <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-2 2xl:grid-cols-3">
                            @foreach ($tiles as $tile)
                                @include('home.partial.widget.'.$tile['key'], [
                                    'tile' => $tile,
                                    'delay' => $loop->index * 60,
                                ])
                            @endforeach
                        </div>
                    @break
                @endswitch
            @endforeach
        </div>
    @else
        <p class="glass mx-auto max-w-md p-6 text-center text-sm font-bold text-slate-500 dark:text-slate-400">
            Everything is hidden.
            <a href="{{ route('settings', ['tab' => 'dashboard']) }}" class="text-violet-600 underline decoration-2 underline-offset-4 hover:text-fuchsia-600 dark:text-fuchsia-400">
                Bring tiles back from Settings
            </a>
        </p>
    @endunless
@endsection
