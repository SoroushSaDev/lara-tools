@extends('layouts.main')

@section('title', 'Search')

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Search</x-slot:title>
        <x-slot:subtitle>
            {{ $count === 0 ? 'Nothing found' : $count.' '.str('match')->plural($count) }}
            @if (filled($query)) · “{{ $query }}” @endif
        </x-slot:subtitle>

        <x-slot:toolbar>
            <x-search-box :action="route('search')" frame="global-search-results" :value="$query"
                          class="max-w-md" placeholder="Search notes, lists and music…" label="Search everything"/>
        </x-slot:toolbar>
    </x-page-header>

    <div class="glass max-w-2xl p-3">
        @include('search.partial.results')
    </div>
@endsection
