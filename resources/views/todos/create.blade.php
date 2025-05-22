@extends('layouts.main')
@section('header-s')
    <a href="{{ route('todos.index') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="hidden lg:block">
            Todo List
        </span>
    </a>
@endsection
@section('title', 'New Todo')
@section('header-e')
    <button type="button"
            class="flex items-center space-x-2 text-xl hover:text-green-500 hover:cursor-pointer"
            onClick="document.querySelector('#todo').submit()">
        <i class="bi bi-check2-circle"></i>
        <span class="hidden lg:block">
            Save
        </span>
    </button>
@endsection
@section('content')
    <form id="todo" class="flex flex-col space-y-3 w-full h-100" method="POST" action="{{ route('todos.store') }}">
        @CSRF
        <input type="text" name="title"
               class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 placeholder-gray-300 border-none rounded-md p-3"
               placeholder="Title"/>
        <textarea name="description"
                  class="font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 placeholder-gray-300 border-none rounded-md resize-none p-3"
                  placeholder="Description"></textarea>
        <div class="grid grid-cols-3 gap-2 px-3">
            <h3 class="text-center text-2xl font-semibold col-start-2">
                Items
            </h3>
            <button type="button" class="flex justify-end items-center hover:text-gray-300 hover:cursor-pointer" onclick="AddTodo();">
                <i class="bi bi-plus-lg"></i>
                <span class="hidden lg:block ms-3">
                    Add Item
                </span>
            </button>
        </div>
        <ul id="todos" class="flex flex-col space-y-3">
            <li class="todo">
                <input type="text" name="items[]" onkeypress="Enter(event);"
                       class="w-full font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 placeholder-gray-300 border-none rounded-md p-3"
                       placeholder="Write a todo..."/>
            </li>
        </ul>
    </form>
@endsection
@push('script')
    <script>
        const element = document.querySelector('.todo').cloneNode(true);

        function Enter(event) {
            if (event.keyCode === 13)
                AddTodo();
        }

        function AddTodo() {
            const newElement = document.querySelector('.todo').cloneNode(true);
            document.getElementById('todos').prepend(newElement);
            const todo = newElement.childNodes[1];
            todo.value = '';
            todo.focus();
        }
    </script>
@endpush
