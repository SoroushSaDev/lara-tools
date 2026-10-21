@extends('layouts.main')

@section('title', 'Notes')

@section('content')
    <x-page-header>
        <x-slot:title>Notes</x-slot:title>
        <x-slot:subtitle>{{ $total }} saved</x-slot:subtitle>

        <x-slot:actions>
            <a href="{{ route('settings', ['tab' => 'notes']) }}" class="icon-btn" title="Notes settings">
                <i class="bi bi-gear"></i>
            </a>

            <a href="{{ route('notes.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i><span>New note</span>
            </a>
        </x-slot:actions>

        <x-slot:toolbar>
            <x-search-box :action="route('notes.index')" frame="notes-results" :value="$query"
                          class="max-w-md" placeholder="Search titles and note text…" label="Search notes"/>
        </x-slot:toolbar>
    </x-page-header>

    @include('notes.partial.results')
@endsection
