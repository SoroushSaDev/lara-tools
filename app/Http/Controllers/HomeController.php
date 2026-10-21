<?php

namespace App\Http\Controllers;

use App\Contracts\SettingRepository;
use App\Models\City;
use App\Models\Event;
use App\Models\Music;
use App\Models\Note;
use App\Models\Todo;
use App\Support\DashboardOrder;
use App\Support\RichText;
use Illuminate\Contracts\View\View;

/**
 * Renders the application dashboard.
 *
 * The dashboard is a stack of sections — counters, shortcut tiles and
 * per-module widgets — each of which the user can hide wholesale, reorder, or
 * silence one tile at a time in Settings › Dashboard. Section order drives
 * everything, and only the sections that are switched on run queries.
 */
class HomeController extends Controller
{
    /**
     * Widget definitions in their default order: what a card is called, the
     * glyph and accent it wears, and where its header links.
     *
     * @var array<string, array{label: string, icon: string, route: string, gradient: string}>
     */
    protected const WIDGETS = [
        'notes' => ['label' => 'Notes', 'icon' => 'bi-journal-text', 'route' => 'notes.index', 'gradient' => 'from-amber-400 to-orange-500'],
        'todos' => ['label' => 'Todo Lists', 'icon' => 'bi-list-check', 'route' => 'todos.index', 'gradient' => 'from-emerald-400 to-teal-600'],
        'calendar' => ['label' => 'Calendar', 'icon' => 'bi-calendar-date', 'route' => 'calendar.index', 'gradient' => 'from-violet-500 to-purple-600'],
        'music' => ['label' => 'Music', 'icon' => 'bi-music-player', 'route' => 'music.index', 'gradient' => 'from-pink-400 to-fuchsia-600'],
        'weather' => ['label' => 'Weather', 'icon' => 'bi-cloud-sun', 'route' => 'weather.index', 'gradient' => 'from-sky-400 to-blue-600'],
        'clock' => ['label' => 'Clock', 'icon' => 'bi-clock', 'route' => 'clock', 'gradient' => 'from-blue-400 to-indigo-600'],
        'calculator' => ['label' => 'Calculator', 'icon' => 'bi-calculator', 'route' => 'calculator', 'gradient' => 'from-lime-400 to-green-600'],
        'converter' => ['label' => 'Converter', 'icon' => 'bi-arrow-left-right', 'route' => 'converter', 'gradient' => 'from-cyan-400 to-sky-600'],
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SettingRepository $settings,
    ) {}

    /**
     * Show the dashboard with the sections the user switched on, in their order.
     *
     * @return list<array{key: string, tiles: list<array<string, mixed>>}>
     */
    public function index(): View
    {
        $builders = [
            'stats' => fn (): array => $this->visible($this->statTiles(), 'dashboard_stat', 'dashboard_order_stats'),
            'shortcuts' => fn (): array => $this->visible($this->shortcutTiles(), 'dashboard_shortcut', 'dashboard_order_shortcuts'),
            'widgets' => fn (): array => $this->widgetTiles(),
        ];

        $sections = [];

        foreach (DashboardOrder::decode($this->settings->get('dashboard_order_sections'), array_keys($builders)) as $key) {
            if (! $this->on("dashboard_show_{$key}")) {
                continue;
            }

            $tiles = $builders[$key]();

            if ($tiles !== []) {
                $sections[] = ['key' => $key, 'tiles' => $tiles];
            }
        }

        return view('home.index', [
            'sections' => $sections,
            'allHidden' => $sections === [],
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
     * Preview cards for the modules the user left switched on, in their order.
     *
     * @return list<array<string, mixed>>
     */
    protected function widgetTiles(): array
    {
        $tiles = [];

        foreach (DashboardOrder::decode($this->settings->get('dashboard_order_widgets'), array_keys(self::WIDGETS)) as $key) {
            if (! $this->on("dashboard_widget_{$key}")) {
                continue;
            }

            $tiles[] = [
                ...self::WIDGETS[$key],
                'key' => $key,
                'route' => route(self::WIDGETS[$key]['route']),
                'data' => $this->widgetData($key),
            ];
        }

        return $tiles;
    }

    /**
     * The live rows a widget shows. Read-only and local: nothing here may
     * reach the network, or the dashboard would wait on another service.
     *
     * @return array<string, mixed>
     */
    protected function widgetData(string $key): array
    {
        return match ($key) {
            'notes' => [
                'items' => Note::latest()->take(4)->get()->map(fn (Note $note): array => [
                    'title' => $note->title ?: 'Untitled',
                    'text' => RichText::toPlainText($note->body, 72),
                    'meta' => $note->updated_at->isoFormat('D MMM'),
                    'route' => route('notes.show', $note),
                ])->all(),
                'empty' => 'No notes yet.',
                'action' => ['label' => 'Write a note', 'route' => route('notes.create')],
            ],
            'todos' => [
                'items' => Todo::latest()->take(3)->get()->map(function (Todo $todo): array {
                    $progress = $todo->progress();

                    return [
                        'title' => $todo->title,
                        'route' => route('todos.show', $todo),
                        'percent' => $progress['percent'],
                        'meta' => "{$progress['completed']} of {$progress['total']} ".str('item')->plural($progress['total']).' done',
                        'overdue' => $todo->isOverdue(),
                    ];
                })->all(),
                'empty' => 'No lists yet.',
                'action' => ['label' => 'New list', 'route' => route('todos.create')],
            ],
            'calendar' => [
                'items' => Event::whereDate('starts_at', '>=', today())
                    ->orderBy('starts_at')
                    ->take(3)
                    ->get()
                    ->map(fn (Event $event): array => [
                        'title' => $event->title,
                        'route' => route('calendar.edit', $event),
                        'meta' => $event->starts_at->isToday()
                            ? 'Today · '.$event->starts_at->format('H:i')
                            : $event->starts_at->isoFormat('ddd D MMM').' · '.$event->starts_at->format('H:i'),
                        'soon' => $event->starts_at->lt(now()->addWeek()),
                    ])
                    ->all(),
                'empty' => 'Nothing coming up.',
                'action' => ['label' => 'Add event', 'route' => route('calendar.create')],
            ],
            'music' => [
                'items' => Music::latest()->take(4)->get()->map(fn (Music $track): array => [
                    'title' => $track->displayTitle(),
                    'meta' => $track->artist ?: ($track->album ?: 'Local file'),
                    'cover' => $track->coverExists() ? $track->coverUrl() : null,
                ])->all(),
                'empty' => 'The library is empty.',
                'action' => ['label' => 'Open Music', 'route' => route('music.index')],
            ],
            'weather' => tap($this->weatherWidget(), fn (array $data): array => $data),
            'clock' => $this->clockWidget(),
            'calculator' => [
                'empty' => 'No calculation yet.',
            ],
            'converter' => [
                // Same glyphs converter.js gives its own category pills.
                'picks' => [
                    ['label' => 'Length', 'icon' => 'bi-rulers'],
                    ['label' => 'Mass', 'icon' => 'bi-box-seam'],
                    ['label' => 'Temperature', 'icon' => 'bi-thermometer-half'],
                    ['label' => 'Data', 'icon' => 'bi-device-hdd'],
                ],
            ],
            default => [],
        };
    }

    /**
     * The home town's last stored reading — deliberately never a live fetch.
     *
     * @return array<string, mixed>
     */
    protected function weatherWidget(): array
    {
        $city = City::where('name', $this->settings->get('home-town'))->first() ?? City::orderBy('name')->first();

        if ($city === null) {
            return [
                'empty' => 'No city saved yet.',
                'action' => ['label' => 'Add a city', 'route' => route('weather.cities.index')],
            ];
        }

        $unit = temperatureUnitSymbol((string) ($this->settings->get('weather_units') ?? 'metric'));

        return [
            'city' => $city->name,
            'country' => $city->country_code,
            'temp' => is_numeric($city->temperature) ? round((float) $city->temperature).$unit : null,
            'feels' => is_numeric($city->feels_like) ? round((float) $city->feels_like).$unit : null,
            'condition' => $city->description ?: $city->main,
            'icon' => weatherIcon($city->main),
            'humidity' => is_numeric($city->humidity) ? (int) $city->humidity.'%' : null,
            'wind' => is_numeric($city->wind_speed) ? number_format((float) $city->wind_speed, 1).' m/s' : null,
            'updated' => $city->updated_at->diffForHumans(),
        ];
    }

    /**
     * The clock widget, rendered with the same format dashboard-widgets.js
     * keeps ticking so the first paint and the first tick agree.
     *
     * @return array<string, mixed>
     */
    protected function clockWidget(): array
    {
        $twelve = $this->settings->get('clock_time_format') === '12h';
        $seconds = $this->on('clock_show_seconds');

        $format = ($twelve ? 'h:i' : 'H:i').($seconds ? ':s' : '').($twelve ? ' A' : '');

        return [
            'time' => now()->format($format),
            'date' => now()->isoFormat('dddd, D MMMM'),
            'format' => $twelve ? '12h' : '24h',
            'seconds' => $seconds,
        ];
    }

    /**
     * Keep the tiles the user switched on, in the order they arranged.
     *
     * @param  array<string, array<string, mixed>>  $tiles
     * @return list<array<string, mixed>>
     */
    protected function visible(array $tiles, string $prefix, string $orderKey): array
    {
        $ordered = [];

        foreach (DashboardOrder::decode($this->settings->get($orderKey), array_keys($tiles)) as $key) {
            if ($this->on("{$prefix}_{$key}")) {
                $ordered[] = $tiles[$key];
            }
        }

        return $ordered;
    }

    /**
     * Whether a switch preference is on; unset means the schema default, on.
     */
    protected function on(string $key): bool
    {
        return ($this->settings->get($key) ?? '1') === '1';
    }
}
