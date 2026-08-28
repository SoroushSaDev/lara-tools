<?php

namespace App\Http\Controllers;

use App\Contracts\SettingRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Application preferences, organised as one tab per tool.
 *
 * TABS drives everything: validation rules, the rendered form and the
 * values shown. Adding a preference is a one-line change here plus the
 * code that consumes it.
 */
class SettingsController extends Controller
{
    /**
     * Preference schema grouped by tool tab.
     *
     * @var array<string, array{label: string, icon: string, fields: array<string, array{type: string, values?: list<string>, options?: array<string, string>, default: string|int}>}>
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
            ],
        ],
        'todos' => [
            'label' => 'Todo Lists',
            'icon' => 'bi-list-check',
            'fields' => [],
        ],
        'calendar' => [
            'label' => 'Calendar',
            'icon' => 'bi-calendar-event',
            'fields' => [
                'calendar_week_start' => [
                    'type' => 'radio',
                    'options' => ['0' => 'Sunday', '1' => 'Monday'],
                    'default' => '0',
                ],
                'calendar_show_holidays' => [
                    'type' => 'switch',
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
                if ($field['type'] !== 'switch') {
                    $rules[$key] = ['sometimes', Rule::in(array_keys($field['options']))];
                }
            }
        }

        $validated = $request->validate($rules);

        foreach (self::TABS as $tab) {
            foreach ($tab['fields'] as $key => $field) {
                if ($field['type'] === 'switch') {
                    // Switches are absent from the payload when unchecked;
                    // persist them as explicit "off" either way.
                    $this->settings->set($key, $request->boolean($key) ? '1' : '0');

                    continue;
                }

                // Radio groups only persist when actually submitted, so
                // partial payloads never reset untouched preferences.
                if (array_key_exists($key, $validated)) {
                    $this->settings->set($key, (string) $validated[$key]);
                }
            }
        }

        return back()->with('success', 'Settings saved.');
    }
}
