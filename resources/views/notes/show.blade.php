@extends('layouts.main')

@section('title', $note->title ?? 'Note')

@section('content')
    <x-page-header back="{{ route('notes.index') }}" back-label="Notes">
        <x-slot:title>{{ str($note->title ?? 'Untitled')->limit(28) }}</x-slot:title>
        <x-slot:subtitle>Last edited {{ $note->updated_at?->diffForHumans() }}</x-slot:subtitle>

        <x-slot:actions>
            <a href="{{ route('notes.edit', $note) }}" class="icon-btn hover:!bg-amber-400/20" title="Edit note">
                <i class="bi bi-pencil"></i>
            </a>

            <form method="POST" action="{{ route('notes.destroy', $note) }}">
                @csrf
                @method('DELETE')

                <button type="submit" data-confirm="Move this note to the recycle bin?" class="icon-btn hover:!text-rose-500" title="Delete note">
                    <i class="bi bi-trash3"></i>
                </button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <article class="glass min-h-[max(22rem,calc(100vh-22rem))] whitespace-pre-line p-6 leading-relaxed animate-fade-up sm:p-8">
        {{ $note->body }}
    </article>
@endsection
