@extends('layouts.main')

@section('title', 'Edit Todo List')

@section('content')
    <x-page-header back="{{ route('todos.show', $todo) }}" back-label="Todo List">
        <x-slot:title>Edit Todo List</x-slot:title>

        <x-slot:actions>
            <button type="button" class="btn-primary" onclick="document.getElementById('todo-form').requestSubmit()">
                <i class="bi bi-check-lg"></i><span>Save</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    @include('todos.partial.form', ['action' => route('todos.update', $todo), 'todo' => $todo, 'items' => $todo->items()])
@endsection
