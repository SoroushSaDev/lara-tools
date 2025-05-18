@extends('layouts.main')
@section('header-s')
    <a href="{{ route('home') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="invisible lg:visible">
            Home
        </span>
    </a>
@endsection
@section('title', 'Todo List')
@section('header-e')
    <a href="{{ route('todos.create') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-plus-lg"></i>
        <span class="hidden lg:block">
            New Todo
        </span>
    </a>
@endsection
@section('content')
    <div class="flex flex-wrap gap-3 lg:gap-6">
        @forelse ($todos as $todo)
            <a href="{{ route('todos.show', $todo) }}"
               class="flex flex-col space-y-3 lg:space-y-6 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-md p-3 lg:p-5 hover:cursor-pointer hover:shadow-2xl">
                <span class="font-semibold text-xl">
                    {{ $todo->title }}
                </span>
                <p>
                    {{ $todo->description }}
                </p>
            </a>
        @empty
            <p class="font-semibold">
                You don't have any todos yet
            </p>
        @endforelse
    </div>
@endsection
