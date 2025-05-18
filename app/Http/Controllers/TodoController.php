<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $todos = Todo::all();
        return view('todos.index', compact('todos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('todos.create');
    }

    /**
     * @param Request $request
     * @return RedirectResponse|void
     * @throws Throwable
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'items.*' => 'required',
            'description' => 'nullable',
        ]);
        DB::beginTransaction();
        try {
            $items = json_encode($request['items']);
            Todo::create([
                'title' => $request['title'],
                'description' => $request['description'],
                'items' => $items,
            ]);
            DB::commit();
            return redirect()->route('todos.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Todo $todo)
    {
        return view('todos.show', compact('todo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Todo $todo)
    {
        return view('todos.edit', compact('todo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Todo $todo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Todo $todo)
    {
        //
    }
}
