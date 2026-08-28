@extends('layouts.main')

@section('title', 'Edit Note')

@section('content')
    <x-page-header back="{{ route('notes.show', $note) }}" back-label="Note">
        <x-slot:title>Edit Note</x-slot:title>

        <x-slot:actions>
            <button type="button" class="btn-primary" onclick="document.getElementById('note-form').requestSubmit()">
                <i class="bi bi-check-lg"></i><span>Save</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    @include('notes.partial.form', ['action' => route('notes.update', $note), 'note' => $note])
@endsection
