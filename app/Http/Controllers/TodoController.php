<?php

namespace App\Http\Controllers;

use App\Contracts\SettingRepository;
use App\Http\Requests\TodoRequest;
use App\Models\Todo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Full CRUD for checklists, including toggling individual items.
 */
class TodoController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SettingRepository $settings,
    ) {}

    /**
     * Display the todo lists in the order the user asked for.
     */
    public function index(): View
    {
        $query = Todo::query();

        // Undated lists sink to the bottom; `due_at is null` keeps NULLs last
        // on both SQLite and MySQL, where bare NULL ordering differs.
        $todos = match ($this->settings->get('todos_sort', 'newest')) {
            'priority' => $query
                ->orderByRaw("case priority when 'high' then 0 when 'medium' then 1 when 'low' then 2 else 3 end")
                ->orderByRaw('due_at is null, due_at')
                ->orderByDesc('created_at')
                ->get(),
            'due' => $query
                ->orderByRaw('due_at is null, due_at')
                ->orderByDesc('created_at')
                ->get(),
            default => $query->latest()->get(),
        };

        $hidden = 0;

        if ($this->settings->get('todos_hide_completed', '0') === '1') {
            $visible = $todos->filter(function (Todo $todo): bool {
                $progress = $todo->progress();

                return $progress['total'] === 0 || $progress['completed'] < $progress['total'];
            });

            $hidden = $todos->count() - $visible->count();
            $todos = $visible;
        }

        return view('todos.index', compact('todos', 'hidden'));
    }

    /**
     * Show the form for creating a new todo list.
     */
    public function create(): View
    {
        return view('todos.create');
    }

    /**
     * Store a newly created todo list.
     */
    public function store(TodoRequest $request): RedirectResponse
    {
        $todo = Todo::create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'due_at' => $request->validated('due_at'),
            'priority' => $request->validated('priority') ?? 'none',
            'items' => collect($request->validated('items') ?? [])
                ->filter(fn ($text) => filled($text))
                ->map(fn ($text) => ['text' => trim($text), 'completed' => false])
                ->values()
                ->all(),
        ]);

        return redirect()
            ->route('todos.show', $todo)
            ->with('success', 'Todo list created.');
    }

    /**
     * Display the specified todo list.
     */
    public function show(Todo $todo): View
    {
        return view('todos.show', compact('todo'));
    }

    /**
     * Show the form for editing the specified todo list.
     */
    public function edit(Todo $todo): View
    {
        return view('todos.edit', compact('todo'));
    }

    /**
     * Update the specified todo list.
     *
     * Items keep their completion state when their text is unchanged so
     * editing a checklist never wipes the user's progress (a bug in the
     * previous implementation).
     */
    public function update(TodoRequest $request, Todo $todo): RedirectResponse
    {
        $previous = collect($todo->items())->keyBy('text');

        $todo->update([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'due_at' => $request->validated('due_at'),
            'priority' => $request->validated('priority') ?? 'none',
            'items' => collect($request->validated('items') ?? [])
                ->filter(fn ($text) => filled($text))
                ->map(fn ($text) => [
                    'text' => trim($text),
                    'completed' => $previous[trim($text)]['completed'] ?? false,
                ])
                ->values()
                ->all(),
        ]);

        return redirect()
            ->route('todos.show', $todo)
            ->with('success', 'Todo list updated.');
    }

    /**
     * Soft-delete the specified todo list.
     */
    public function destroy(Todo $todo): RedirectResponse
    {
        $todo->delete();

        return redirect()
            ->route('todos.index')
            ->with('success', 'Todo list moved to the recycle bin.');
    }

    /**
     * Toggle the completed state of a single checklist item.
     */
    public function toggleItem(Todo $todo, int $index): JsonResponse
    {
        $items = $todo->items();

        if (! isset($items[$index])) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $items[$index]['completed'] = ! $items[$index]['completed'];

        $todo->update(['items' => $items]);

        return response()->json([
            'completed' => $items[$index]['completed'],
            'progress' => $todo->progress(),
        ]);
    }
}
