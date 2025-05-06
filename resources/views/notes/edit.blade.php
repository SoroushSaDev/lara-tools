@extends('layouts.main')
@section('header-s')
    <a href="{{ route('notes.show', $note) }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="invisible lg:visible">
            Note
        </span>
    </a>
@endsection
@section('title', 'Edit Note')
@section('header-e')
    <button type="button"
        class="flex items-center space-x-2 text-xl hover:text-green-500 hover:cursor-pointer"
        onClick="document.querySelector('#note').submit()">
        <i class="bi bi-check2-circle"></i>
        <span class="hidden lg:block">
            Save
        </span>
    </button>
@endsection
@section('content')
    <form id="note" class="flex flex-col space-y-3 w-full h-100" method="POST" action="{{ route('notes.update', $note) }}">
        @CSRF
        @method('PUT')
        <input type="text" name="title" class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-md p-3"
            placeholder="Title" value="{{ $note->title }}"/>
        <textarea name="body" class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-md h-full resize-none p-3"
            placeholder="Body">{{ $note->body }}</textarea>
    </form>
@endsection
