<?php

namespace App\Http\Controllers;

use App\Contracts\SettingRepository;
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
     * Create a new controller instance.
     */
    public function __construct(
        protected SettingRepository $settings,
    ) {}

    /**
     * Show the dashboard with quick access tiles and live counters.
     */
    public function index(): View
    {
        return view('home.index', [
            'stats' => $this->enabled($this->statTiles(), 'dashboard_stat_'),
            'shortcuts' => $this->enabled($this->shortcutTiles(), 'dashboard_shortcut_'),
            'showStats' => ($this->settings->get('dashboard_show_stats') ?? '1') === '1',
            'showShortcuts' => ($this->settings->get('dashboard_show_shortcuts') ?? '1') === '1',
        ]);
    }

    /**
     * Live counters, keyed by the matching `dashboard_stat_*` preference.
     *
     * @return array<string, array{icon: string, value: int, label: string, gradient: string}>
     */
    protected function statTiles(): array
    {
        $notes = Note::count();
        $openItems = Todo::all()->sum(function (Todo $todo) {
            $progress = $todo->progress();

            return $progress['total'] - $progress['completed'];
        });
        $events = Event::whereDate('starts_at', '>=', today())->count();
        $tracks = Music::count();

        return [
            'notes' => [
                'icon' => 'bi-journal-text',
                'value' => $notes,
                'label' => str('note')->plural($notes),
                'gradient' => 'from-amber-400 to-orange-500',
            ],
            'open_items' => [
                'icon' => 'bi-inbox',
                'value' => $openItems,
                'label' => 'open '.str('item')->plural($openItems),
                'gradient' => 'from-emerald-400 to-teal-500',
            ],
            'events' => [
                'icon' => 'bi-calendar-event',
                'value' => $events,
                'label' => 'upcoming '.str('event')->plural($events),
                'gradient' => 'from-violet-500 to-purple-600',
            ],
            'tracks' => [
                'icon' => 'bi-music-note-beamed',
                'value' => $tracks,
                'label' => str('track')->plural($tracks),
                'gradient' => 'from-pink-400 to-fuchsia-600',
            ],
        ];
    }

    /**
     * Tool launcher tiles, keyed by the matching `dashboard_shortcut_*` preference.
     *
     * @return array<string, array{route: string, icon: string, iconFill: string, label: string, gradient: string, shadow: string}>
     */
    protected function shortcutTiles(): array
    {
        return [
            'notes' => [
                'route' => route('notes.index'),
                'icon' => 'bi-journal-text',
                'iconFill' => 'bi-journal-text',
                'label' => 'Notes',
                'gradient' => 'from-amber-400 to-orange-500',
                'shadow' => 'shadow-orange-500/30',
            ],
            'todos' => [
                'route' => route('todos.index'),
                'icon' => 'bi-list-check',
                'iconFill' => 'bi-list-check',
                'label' => 'Todo Lists',
                'gradient' => 'from-emerald-400 to-teal-600',
                'shadow' => 'shadow-emerald-500/30',
            ],
            'calendar' => [
                'route' => route('calendar.index'),
                'icon' => 'bi-calendar-date',
                'iconFill' => 'bi-calendar-date-fill',
                'label' => 'Calendar',
                'gradient' => 'from-violet-500 to-purple-600',
                'shadow' => 'shadow-violet-500/30',
            ],
            'music' => [
                'route' => route('music.index'),
                'icon' => 'bi-music-player',
                'iconFill' => 'bi-music-player-fill',
                'label' => 'Music Player',
                'gradient' => 'from-pink-400 to-fuchsia-600',
                'shadow' => 'shadow-fuchsia-500/30',
            ],
            'weather' => [
                'route' => route('weather.index'),
                'icon' => 'bi-cloud-sun',
                'iconFill' => 'bi-cloud-sun-fill',
                'label' => 'Weather',
                'gradient' => 'from-sky-400 to-blue-600',
                'shadow' => 'shadow-sky-500/30',
            ],
            'calculator' => [
                'route' => route('calculator'),
                'icon' => 'bi-calculator',
                'iconFill' => 'bi-calculator-fill',
                'label' => 'Calculator',
                'gradient' => 'from-lime-400 to-green-600',
                'shadow' => 'shadow-lime-500/30',
            ],
            'clock' => [
                'route' => route('clock'),
                'icon' => 'bi-clock',
                'iconFill' => 'bi-clock-fill',
                'label' => 'Clock',
                'gradient' => 'from-blue-400 to-indigo-600',
                'shadow' => 'shadow-indigo-500/30',
            ],
            'converter' => [
                'route' => route('converter'),
                'icon' => 'bi-calculator',
                'iconFill' => 'bi-calculator-fill',
                'label' => 'Unit Converter',
                'gradient' => 'from-cyan-400 to-sky-600',
                'shadow' => 'shadow-cyan-500/30',
            ],
        ];
    }

    /**
     * Drop the tiles the user switched off, keeping the declared order.
     *
     * @param  array<string, array<string, mixed>>  $tiles
     * @return list<array<string, mixed>>
     */
    protected function enabled(array $tiles, string $prefix): array
    {
        return array_values(array_filter(
            $tiles,
            fn (array $tile, string $key): bool => ($this->settings->get($prefix.$key) ?? '1') === '1',
            ARRAY_FILTER_USE_BOTH
        ));
    }
}
