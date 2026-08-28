@extends('layouts.main')

@section('title', 'Add Event')

@section('content')
    <x-page-header back="{{ route('calendar.index', ['date' => $date->toDateString()]) }}" back-label="Calendar">
        <x-slot:title>Add Event</x-slot:title>

        <x-slot:actions>
            <button type="button" class="btn-primary" onclick="document.getElementById('event-form').requestSubmit()">
                <i class="bi bi-check-lg"></i><span>Save</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    @include('calendar.partial.form', ['action' => route('calendar.store'), 'date' => $date])
@endsection
