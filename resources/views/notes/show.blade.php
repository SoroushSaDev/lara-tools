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

                <button type="submit" data-confirm="Move this note to the recycle bin?" class="icon-btn icon-btn-danger" title="Delete note">
                    <i class="bi bi-trash3"></i>
                </button>
            </form>
        </x-slot:actions>
    </x-page-header>

    {{-- Re-sanitised on the way out on purpose: rows saved before the rich-text
         editor (and plain-text notes) are upgraded to paragraphs here. --}}
    <article class="glass rt-body rt-rendered min-h-[max(22rem,calc(100vh-22rem))] p-6 leading-relaxed animate-fade-up sm:p-8">
        {!! App\Support\RichText::render($note->body) !!}
    </article>
@endsection
