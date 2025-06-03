@extends('layouts.main')
@section('header-s')
    <a href="{{ route('notes.index') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="hidden lg:block">
            Notes
        </span>
    </a>
@endsection
@section('title', 'Note')
@section('header-e')
    <a href="{{ route('notes.edit', $note) }}"
       class="flex items-center space-x-2 text-xl hover:text-yellow-500 hover:cursor-pointer">
        <i class="bi bi-pencil-fill"></i>
        <span class="hidden lg:block">
            Edit Note
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
    <div class="flex flex-col space-y-3 h-100 max-sm:w-full sm:w-[600px]">
        <span class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-md p-3">
            {{ $note->title }}
        </span>
        <p class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-md h-full resize-none p-3">
            {{ $note->body }}
        </p>
    </div>
    <form id="delete" method="POST" action="{{ route('notes.destroy', $note) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection
