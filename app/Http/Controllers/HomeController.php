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
                'openItems' => Todo::all()->sum(function (Todo $todo) {
                    $progress = $todo->progress();

                    return $progress['total'] - $progress['completed'];
                }),
                'events' => Event::whereDate('starts_at', '>=', today())->count(),
                'tracks' => Music::count(),
            ],
        ]);
    }
}
