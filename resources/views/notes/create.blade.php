@extends('layouts.main')

@section('title', 'New Note')

@section('content')
    <x-page-header back="{{ route('notes.index') }}" back-label="Notes">
        <x-slot:title>New Note</x-slot:title>

        <x-slot:actions>
            <button type="button" class="btn-primary" onclick="document.getElementById('note-form').requestSubmit()">
                <i class="bi bi-check-lg"></i><span>Save</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    @include('notes.partial.form', ['action' => route('notes.store')])
@endsection
