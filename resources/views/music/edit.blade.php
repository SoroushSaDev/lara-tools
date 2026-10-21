@extends('layouts.main')

@section('title', 'Edit Track Details')

@section('content')
    <x-page-header back="{{ route('music.index') }}" back-label="Music">
        <x-slot:title>Edit Track Details</x-slot:title>
        <x-slot:subtitle>{{ $track->name }}</x-slot:subtitle>

        <x-slot:actions>
            <button type="button" class="btn-primary" onclick="document.getElementById('meta-form').requestSubmit()">
                <i class="bi bi-check-lg"></i><span>Save</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    @include('music.partial.form', ['action' => route('music.update', $track), 'track' => $track])
@endsection
