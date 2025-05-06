<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteRequest;
use App\Models\Note;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = Note::all();
        return view('notes.index', compact('notes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('notes.create');
    }

    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        return view('notes.show', compact('note'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note)
    {
        return view('notes.edit', compact('note'));
    }

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
