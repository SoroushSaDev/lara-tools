<?php

namespace App\Http\Controllers;

use App\Contracts\SettingRepository;
use App\Support\DashboardOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Application preferences, organised as one tab per tool.
 *
 * TABS drives everything: validation rules, the rendered form and the
 * values shown. Adding a preference is a one-line change here plus the
 * code that consumes it.
 *
 * A tab may split its fields into sections (presentational only — keys
 * must name fields declared in the same tab), and any field may carry a
 * `label` instead of the name derived from its key. An `order` field lists
 * its tiles in `items` and renders one draggable row per tile; with a
 * `toggle_prefix` each row also embeds that tile's own switch field, so a
 * tile never appears twice in the form.
 */
class SettingsController extends Controller
{
    /**
     * Preference schema grouped by tool tab.
     *
     * @var array<string, array{label: string, icon: string, fields: array<string, array{type: string, options?: array<string, string>, items?: array<string, string>, toggle_prefix?: string, label?: string, default: string|int}>, sections?: list<array{title: string, hint?: string, keys: list<string>}>}>
     */
    protected const TABS = [
        'general' => [
            'label' => 'General',
            'icon' => 'bi-sliders',
            'fields' => [
                'appearance' => [
                    'type' => 'radio',
                    'options' => ['light' => 'Light', 'dark' => 'Dark', 'system' => 'System'],
                    'default' => 'dark',
                ],
                'confirm_deletes' => [
                    'type' => 'switch',
                    'label' => 'Ask before moving anything to the bin',
                    'default' => '1',
                ],
            ],
        ],
        'dashboard' => [
            'label' => 'Dashboard',
            'icon' => 'bi-collection',
            'sections' => [
                [
                    'title' => 'Layout',
                    'hint' => 'Drag the groups into the order they should appear.',
                    'keys' => [
                        'dashboard_order_sections',
                    ],
                ],
                [
                    'title' => 'Statistics',
                    'hint' => 'The counters row on top of the dashboard.',
                    'keys' => [
                        'dashboard_show_stats',
                        'dashboard_order_stats',
                    ],
                ],
                [
                    'title' => 'Shortcuts',
                    'hint' => 'The tool tiles that open every module.',
                    'keys' => [
                        'dashboard_show_shortcuts',
                        'dashboard_order_shortcuts',
                    ],
                ],
                [
                    'title' => 'Widgets',
                    'hint' => 'Live preview cards, one per module.',
                    'keys' => [
                        'dashboard_show_widgets',
                        'dashboard_order_widgets',
                    ],
                ],
            ],
            'fields' => [
                'dashboard_order_sections' => [
                    'type' => 'order',
                    'label' => 'Group order',
                    'items' => [
                        'stats' => 'Statistics',
                        'shortcuts' => 'Shortcuts',
                        'widgets' => 'Widgets',
                    ],
                    'default' => '',
                ],
                'dashboard_show_stats' => [
                    'type' => 'switch',
                    'label' => 'Show statistics',
                    'default' => '1',
                ],
                'dashboard_stat_notes' => [
                    'type' => 'switch',
                    'label' => 'Notes',
                    'default' => '1',
                ],
                'dashboard_stat_open_items' => [
                    'type' => 'switch',
                    'label' => 'Open items',
                    'default' => '1',
                ],
                'dashboard_stat_events' => [
                    'type' => 'switch',
                    'label' => 'Upcoming events',
                    'default' => '1',
                ],
                'dashboard_stat_tracks' => [
                    'type' => 'switch',
                    'label' => 'Tracks',
                    'default' => '1',
                ],
                'dashboard_order_stats' => [
                    'type' => 'order',
                    'label' => 'Counters',
                    'toggle_prefix' => 'dashboard_stat_',
                    'items' => [
                        'notes' => 'Notes',
                        'open_items' => 'Open items',
                        'events' => 'Upcoming events',
                        'tracks' => 'Tracks',
                    ],
                    'default' => '',
                ],
                'dashboard_show_shortcuts' => [
                    'type' => 'switch',
                    'label' => 'Show shortcuts',
                    'default' => '1',
                ],
                'dashboard_shortcut_notes' => [
                    'type' => 'switch',
                    'label' => 'Notes',
                    'default' => '1',
                ],
                'dashboard_shortcut_todos' => [
                    'type' => 'switch',
                    'label' => 'Todo Lists',
                    'default' => '1',
                ],
                'dashboard_shortcut_calendar' => [
                    'type' => 'switch',
                    'label' => 'Calendar',
                    'default' => '1',
                ],
                'dashboard_shortcut_music' => [
                    'type' => 'switch',
                    'label' => 'Music Player',
                    'default' => '1',
                ],
                'dashboard_shortcut_weather' => [
                    'type' => 'switch',
                    'label' => 'Weather',
                    'default' => '1',
                ],
                'dashboard_shortcut_calculator' => [
                    'type' => 'switch',
                    'label' => 'Calculator',
                    'default' => '1',
                ],
                'dashboard_shortcut_clock' => [
                    'type' => 'switch',
                    'label' => 'Clock',
                    'default' => '1',
                ],
                'dashboard_shortcut_converter' => [
                    'type' => 'switch',
                    'label' => 'Unit Converter',
                    'default' => '1',
                ],
                'dashboard_order_shortcuts' => [
                    'type' => 'order',
                    'label' => 'Tool tiles',
                    'toggle_prefix' => 'dashboard_shortcut_',
                    'items' => [
                        'notes' => 'Notes',
                        'todos' => 'Todo Lists',
                        'calendar' => 'Calendar',
                        'music' => 'Music Player',
                        'weather' => 'Weather',
                        'calculator' => 'Calculator',
                        'clock' => 'Clock',
                        'converter' => 'Unit Converter',
                    ],
                    'default' => '',
                ],
                'dashboard_show_widgets' => [
                    'type' => 'switch',
                    'label' => 'Show widgets',
                    'default' => '1',
                ],
                'dashboard_widget_notes' => [
                    'type' => 'switch',
                    'label' => 'Notes',
                    'default' => '1',
                ],
                'dashboard_widget_todos' => [
                    'type' => 'switch',
                    'label' => 'Todo Lists',
                    'default' => '1',
                ],
                'dashboard_widget_calendar' => [
                    'type' => 'switch',
                    'label' => 'Calendar',
                    'default' => '1',
                ],
                'dashboard_widget_music' => [
                    'type' => 'switch',
                    'label' => 'Music',
                    'default' => '1',
                ],
                'dashboard_widget_weather' => [
                    'type' => 'switch',
                    'label' => 'Weather',
                    'default' => '1',
                ],
                'dashboard_widget_clock' => [
                    'type' => 'switch',
                    'label' => 'Clock',
                    'default' => '1',
                ],
                'dashboard_widget_calculator' => [
                    'type' => 'switch',
                    'label' => 'Calculator',
                    'default' => '1',
                ],
                'dashboard_widget_converter' => [
                    'type' => 'switch',
                    'label' => 'Converter',
                    'default' => '1',
                ],
                'dashboard_order_widgets' => [
                    'type' => 'order',
                    'label' => 'Preview cards',
                    'toggle_prefix' => 'dashboard_widget_',
                    'items' => [
                        'notes' => 'Recent notes',
                        'todos' => 'Active checklists',
                        'calendar' => 'Upcoming events',
                        'music' => 'Recently imported',
                        'weather' => 'Home town conditions',
                        'clock' => 'Local time and alarms',
                        'calculator' => 'Last calculation',
                        'converter' => 'Quick unit picks',
                    ],
                    'default' => '',
                ],
            ],
        ],
        'notes' => [
            'label' => 'Notes',
            'icon' => 'bi-journal-text',
            'fields' => [
                'notes_sort' => [
                    'type' => 'radio',
                    'options' => ['newest' => 'Newest first', 'oldest' => 'Oldest first', 'title' => 'By title'],
                    'default' => 'newest',
                ],
                'notes_excerpt_length' => [
                    'type' => 'radio',
                    'label' => 'Card excerpt length',
                    'options' => ['80' => '80 characters', '160' => '160 characters', '240' => '240 characters', '400' => '400 characters'],
                    'default' => '160',
                ],
                'notes_toolbar_compact' => [
                    'type' => 'switch',
                    'label' => 'Compact formatting toolbar',
                    'default' => '0',
                ],
            ],
        ],
        'todos' => [
            'label' => 'Todo Lists',
            'icon' => 'bi-list-check',
            'fields' => [
                'todos_sort' => [
                    'type' => 'radio',
                    'label' => 'Order lists by',
                    'options' => ['newest' => 'Newest first', 'priority' => 'Priority', 'due' => 'Due date'],
                    'default' => 'newest',
                ],
                'todos_hide_completed' => [
                    'type' => 'switch',
                    'label' => 'Hide lists with every item done',
                    'default' => '0',
                ],
            ],
        ],
        'calendar' => [
            'label' => 'Calendar',
            'icon' => 'bi-calendar-event',
            'fields' => [
                'calendar_week_start' => [
                    'type' => 'radio',
                    'options' => ['0' => 'Sunday', '1' => 'Monday', '6' => 'Saturday'],
                    'default' => '0',
                ],
                'calendar_show_holidays' => [
                    'type' => 'switch',
                    'label' => 'Show public holidays',
                    'default' => '1',
                ],
                'calendar_show_locations' => [
                    'type' => 'switch',
                    'label' => 'Show event locations',
                    'default' => '1',
                ],
            ],
        ],
        'music' => [
            'label' => 'Music',
            'icon' => 'bi-music-note-beamed',
            'fields' => [
                'music_shuffle_default' => [
                    'type' => 'switch',
                    'label' => 'Start shuffled',
                    'default' => '0',
                ],
            ],
        ],
        'weather' => [
            'label' => 'Weather',
            'icon' => 'bi-cloud-sun',
            'fields' => [
                'weather_units' => [
                    'type' => 'radio',
                    'options' => ['metric' => '°C · Metric', 'imperial' => '°F · Imperial'],
                    'default' => 'metric',
                ],
                'weather_show_technical' => [
                    'type' => 'switch',
                    'label' => 'Show the technical details card',
                    'default' => '1',
                ],
            ],
        ],
        'clock' => [
            'label' => 'Clock',
            'icon' => 'bi-clock',
            'fields' => [
                'clock_time_format' => [
                    'type' => 'radio',
                    'options' => ['24h' => '24-hour', '12h' => '12-hour'],
                    'default' => '24h',
                ],
                'clock_show_seconds' => [
                    'type' => 'switch',
                    'label' => 'Tick the seconds',
                    'default' => '1',
                ],
                'clock_show_analog' => [
                    'type' => 'switch',
                    'label' => 'Show the analog face',
                    'default' => '1',
                ],
            ],
        ],
        'converter' => [
            'label' => 'Converter',
            'icon' => 'bi-calculator',
            'fields' => [
                'converter_precision' => [
                    'type' => 'radio',
                    'options' => ['1' => '1 decimal', '2' => '2 decimals', '3' => '3 decimals', '4' => '4 decimals'],
                    'default' => '2',
                ],
            ],
        ],
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SettingRepository $settings,
    ) {}

    /**
     * Display the tabbed settings page.
     */
    public function index(): View
    {
        return view('settings.index', [
            'tabs' => collect(self::TABS)->map(fn (array $tab, string $tabKey) => [
                'key' => $tabKey,
                ...$tab,
                'fields' => collect($tab['fields'])->map(fn (array $field, string $fieldKey) => [
                    'key' => $fieldKey,
                    ...$field,
                    'current' => (string) ($this->settings->get($fieldKey) ?? $field['default']),
                ])->toArray(),
            ])->toArray(),
        ]);
    }

    /**
     * Persist the submitted preferences.
     */
    public function update(Request $request): RedirectResponse
    {
        // Validate everything up front so every offending field gets an error
        // instead of stopping at the first invalid tab.
        $rules = [];

        foreach (self::TABS as $tab) {
            foreach ($tab['fields'] as $key => $field) {
                if ($field['type'] === 'switch') {
                    continue;
                }

                if ($field['type'] === 'order') {
                    // An order travels as a JSON array of tile keys; anything
                    // malformed is normalised against the tab's canonical list
                    // when it is saved, so the shape only has to be bounded.
                    $rules[$key] = ['sometimes', 'string', 'max:255'];

                    continue;
                }

                $rules[$key] = ['sometimes', Rule::in(array_keys($field['options']))];
            }
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($request, $validated) {
            foreach (self::TABS as $tab) {
                foreach ($tab['fields'] as $key => $field) {
                    if ($field['type'] === 'switch') {
                        // The view pairs every checkbox with a hidden "0"
                        // input, so a submitted tab always carries the key;
                        // partial payloads never reset untouched switches.
                        if ($request->has($key)) {
                            $this->settings->set($key, $request->boolean($key) ? '1' : '0');
                        }

                        continue;
                    }

                    if ($field['type'] === 'order') {
                        if (array_key_exists($key, $validated)) {
                            $this->settings->set($key, DashboardOrder::encode(
                                $validated[$key],
                                array_keys($field['items']),
                            ));
                        }

                        continue;
                    }

                    // Radio groups only persist when actually submitted, so
                    // partial payloads never reset untouched preferences.
                    if (array_key_exists($key, $validated)) {
                        $this->settings->set($key, (string) $validated[$key]);
                    }
                }
            }
        });

        return back()->with('success', 'Settings saved.');
    }
}
