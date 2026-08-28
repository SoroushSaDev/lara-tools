@extends('layouts.main')

@section('title', 'Edit Event')

@section('content')
    <x-page-header back="{{ route('calendar.index', ['date' => $event->starts_at->toDateString()]) }}" back-label="Calendar">
        <x-slot:title>Edit Event</x-slot:title>

        <x-slot:actions>
            <button type="button" class="btn-primary" onclick="document.getElementById('event-form').requestSubmit()">
                <i class="bi bi-check-lg"></i><span>Save</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    @include('calendar.partial.form', ['action' => route('calendar.update', $event), 'date' => $event->starts_at, 'event' => $event])
@endsection
