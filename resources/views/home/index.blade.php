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
    @if ($showStats && $stats !== [])
        <div class="mb-6 grid grid-cols-2 gap-3 animate-fade-up sm:gap-4 {{ count($stats) === 1 ? 'sm:grid-cols-2' : 'sm:grid-cols-4' }}" style="animation-delay: 60ms">
            @foreach ($stats as $stat)
                @include('home.partial.stat', $stat)
            @endforeach
        </div>
    @endif

    {{-- Tool launcher grid --}}
    @if ($showShortcuts && $shortcuts !== [])
        <div class="grid grid-cols-2 gap-3 sm:gap-5 md:grid-cols-3 xl:grid-cols-4">
            @foreach ($shortcuts as $shortcut)
                @include('home.partial.item', [...$shortcut, 'delay' => 100 + $loop->index * 60])
            @endforeach
        </div>
    @endif

    @if (! $showStats && ! $showShortcuts)
        <p class="glass mx-auto max-w-md p-6 text-center text-sm font-bold text-slate-500 dark:text-slate-400">
            Everything is hidden.
            <a href="{{ route('settings', ['tab' => 'dashboard']) }}" class="text-violet-600 underline decoration-2 underline-offset-4 hover:text-fuchsia-600 dark:text-fuchsia-400">
                Bring tiles back from Settings
            </a>
        </p>
    @endif
@endsection
