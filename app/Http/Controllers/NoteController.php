<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteRequest;
use App\Models\Note;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class NoteController extends Controller
{
    /**
     * @return Factory|View|Application|\Illuminate\View\View|object
     */
    public function index()
    {
        $notes = Note::all();
        return view('notes.index', compact('notes'));
    }

    /**
     * @return Factory|View|Application|\Illuminate\View\View|object
     */
    public function create()
    {
        return view('notes.create');
    }

    /**
     * @param NoteRequest $request
     * @return RedirectResponse|void
     * @throws Throwable
     */
    public function store(NoteRequest $request)
    {
        DB::beginTransaction();
        try {
            Note::create([
                'title' => $request['title'],
                'body' => $request['body'],
            ]);
            DB::commit();
            return redirect()->route('notes.index');
        } catch(Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }

    /**
     * @param Note $note
     * @return Factory|View|Application|\Illuminate\View\View|object
     */
    public function show(Note $note)
    {
        return view('notes.show', compact('note'));
    }

    /**
     * @param Note $note
     * @return Factory|View|Application|\Illuminate\View\View|object
     */
    public function edit(Note $note)
    {
        return view('notes.edit', compact('note'));
    }

    /**
     * @param NoteRequest $request
     * @param Note $note
     * @return RedirectResponse|void
     * @throws Throwable
     */
    public function update(NoteRequest $request, Note $note)
    {
        DB::beginTransaction();
        try {
            $note->update([
                'title' => $request['title'],
                'body' => $request['body'],
            ]);
            DB::commit();
            return redirect()->route('notes.index');
        } catch(Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }

    /**
     * @param Note $note
     * @return RedirectResponse|void
     * @throws Throwable
     */
    public function destroy(Note $note)
    {
        DB::beginTransaction();
        try {
            $note->delete();
            DB::commit();
            return redirect()->route('notes.index');
        } catch(Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }
}
