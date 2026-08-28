<?php

namespace App\Http\Controllers;

use App\Contracts\SettingRepository;
use App\Http\Requests\NoteRequest;
use App\Models\Note;
use Illuminate\Contracts\View\View;

/**
 * Full CRUD for sticky notes.
 */
class NoteController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SettingRepository $settings,
    ) {}

    /**
     * Display a listing of the notes, honouring the sort preference.
     */
    public function index(): View
    {
        $query = Note::query();

        $notes = match ($this->settings->get('notes_sort', 'newest')) {
            'oldest' => $query->oldest()->get(),
            'title' => $query->orderBy('title')->get(),
            default => $query->latest()->get(),
        };

        return view('notes.index', [
            'notes' => $notes,
        ]);
    }

    /**
     * Show the form for creating a new note.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('notes.create');
    }

    /**
     * Store a newly created note.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(NoteRequest $request)
    {
        Note::create($request->validated());

        return redirect()
            ->route('notes.index')
            ->with('success', 'Note created.');
    }

    /**
     * Display the specified note.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Note $note)
    {
        return view('notes.show', compact('note'));
    }

    /**
     * Show the form for editing the specified note.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Note $note)
    {
        return view('notes.edit', compact('note'));
    }

    /**
     * Update the specified note.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(NoteRequest $request, Note $note)
    {
        $note->update($request->validated());

        return redirect()
            ->route('notes.show', $note)
            ->with('success', 'Note updated.');
    }

    /**
     * Soft-delete the specified note so it can be restored from the bin.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Note $note)
    {
        $note->delete();

        return redirect()
            ->route('notes.index')
            ->with('success', 'Note moved to the recycle bin.');
    }
}
