<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoRequest;
use App\Models\Todo;
use Exception;
use Illuminate\Http\RedirectResponse;
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
     * @param TodoRequest $request
     * @return RedirectResponse|void
     * @throws Throwable
     */
    public function store(TodoRequest $request)
    {
        DB::beginTransaction();
        try {
            $items = [];
            foreach ($request->items as $item) {
                if (!is_null($item))
                    $items[] = [
                        'text' => $item,
                        'completed' => false,
                    ];
            }
            Todo::create([
                'title' => $request['title'],
                'items' => json_encode($items),
                'description' => $request['description'],
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
     * @param TodoRequest $request
     * @param Todo $todo
     * @return RedirectResponse|void
     * @throws Throwable
     */
    public function update(TodoRequest $request, Todo $todo)
    {
        DB::beginTransaction();
        try {
            $items = [];
            foreach ($request->items as $item) {
                if (!is_null($item))
                    $items[] = [
                        'text' => $item,
                        'completed' => false,
                    ];
            }
            $todo->update([
                'title' => $request['title'],
                'items' => json_encode($items),
                'description' => $request['description'],
            ]);
            DB::commit();
            return redirect()->route('todos.show', $todo);
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception);
        }
    }

    /**
     * @param Todo $todo
     * @return RedirectResponse|void
     * @throws Throwable
     */
    public function destroy(Todo $todo)
    {
        DB::beginTransaction();
        try {
            $todo->delete();
            DB::commit();
            return redirect()->route('todos.index');
        } catch (Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }
}
