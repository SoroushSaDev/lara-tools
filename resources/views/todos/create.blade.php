@extends('layouts.main')

@section('title', 'New Todo List')

@section('content')
    <x-page-header back="{{ route('todos.index') }}" back-label="Todo Lists">
        <x-slot:title>New Todo List</x-slot:title>

        <x-slot:actions>
            <button type="button" class="btn-primary" onclick="document.getElementById('todo-form').requestSubmit()">
                <i class="bi bi-check-lg"></i><span>Save</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    @include('todos.partial.form', ['action' => route('todos.store')])
@endsection
