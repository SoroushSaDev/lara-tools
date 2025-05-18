@extends('layouts.main')
@section('header-s')
    <a href="{{ route('todos.index') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="hidden lg:block">
            Todo List
        </span>
    </a>
@endsection
@section('title', 'Todo')
@section('header-e')
    <a href="{{ route('todos.edit', $todo) }}"
       class="flex items-center space-x-2 text-xl hover:text-yellow-500 hover:cursor-pointer">
        <i class="bi bi-pencil-fill"></i>
        <span class="hidden lg:block">
            Edit Todo
        </span>
    </a>
    <button type="button"
            class="flex items-center space-x-2 text-xl hover:text-red-500 hover:cursor-pointer"
            onClick="document.querySelector('#delete').submit()">
        <i class="bi bi-trash3-fill"></i>
        <span class="hidden lg:block">
            Delete
        </span>
    </button>
@endsection
@section('content')
    <div class="flex flex-col space-y-3 w-full h-100">
        <span class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-md p-3">
            {{ $todo->title }}
        </span>
        <p class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-md resize-none p-3">
            {{ $todo->description }}
        </p>
        <h3 class="text-center text-2xl font-semibold col-start-2">
            Items
        </h3>
        @forelse(json_decode($todo->items) as $item)
            <p class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-md resize-none p-3">
                {{ $item }}
            </p>
        @empty
            There are no items
        @endforelse
    </div>
    <form id="delete" method="POST" action="{{ route('todos.destroy', $todo) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection
