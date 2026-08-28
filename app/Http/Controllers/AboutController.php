<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * About page: current version plus the full changelog timeline.
 */
class AboutController extends Controller
{
    /**
     * Show application information and version history.
     */
    public function index(): View
    {
        return view('about.index', [
            'version' => config('app.version'),
            'changelog' => config('changelog.entries'),
        ]);
    }
}
