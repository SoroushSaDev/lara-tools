<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Music;
use App\Models\Note;
use App\Models\Todo;
use Illuminate\Contracts\View\View;

/**
 * Renders the application dashboard.
 */
class HomeController extends Controller
{
    /**
     * Show the dashboard with quick access tiles and live counters.
     */
    public function index(): View
    {
        return view('home.index', [
            'stats' => [
                'notes' => Note::count(),
                'todos' => Todo::count(),
                'openItems' => collect(Todo::all())->sum(fn (Todo $todo) => $todo->progress()['total'] - $todo->progress()['completed']),
                'events' => Event::whereDate('starts_at', '>=', today())->count(),
                'tracks' => Music::count(),
            ],
        ]);
    }
}
