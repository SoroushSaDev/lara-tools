@extends('layouts.main')

@section('title', 'Todo Lists')

@section('content')
    <x-page-header>
        <x-slot:title>Todo Lists</x-slot:title>
        <x-slot:subtitle>
            {{ $total }} {{ str('list')->plural($total) }}
            @if ($hidden > 0) · {{ $hidden }} finished {{ str('list')->plural($hidden) }} hidden @endif
        </x-slot:subtitle>

        <x-slot:actions>
            <a href="{{ route('settings', ['tab' => 'todos']) }}" class="icon-btn" title="Todo settings">
                <i class="bi bi-gear"></i>
            </a>

            <a href="{{ route('todos.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i><span>New list</span>
            </a>
        </x-slot:actions>

        <x-slot:toolbar>
            <x-search-box :action="route('todos.index')" frame="todos-results" :value="$query"
                          class="max-w-md" placeholder="Search lists, descriptions and items…" label="Search todo lists"/>
        </x-slot:toolbar>
    </x-page-header>

    @include('todos.partial.results')
@endsection
