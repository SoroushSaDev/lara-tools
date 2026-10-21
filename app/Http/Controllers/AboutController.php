<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * About page: identity block, quick facts, one card per tool, keyboard
 * behaviour, where the data lives, credits and the version history modal.
 *
 * Everything here comes from config plus the composer/npm manifests — the
 * controller runs no queries, so the page renders on an empty database.
 */
class AboutController extends Controller
{
    /**
     * Anchored sections in render order; also drives the rail jump-nav.
     *
     * @var list<array{id: string, icon: string, label: string}>
     */
    protected const SECTIONS = [
        ['id' => 'overview', 'icon' => 'bi-info-circle', 'label' => 'What LaraTools is'],
        ['id' => 'tools', 'icon' => 'bi-tools', 'label' => 'The tools'],
        ['id' => 'keyboard', 'icon' => 'bi-keyboard', 'label' => 'Keyboard & input'],
        ['id' => 'data', 'icon' => 'bi-hdd', 'label' => 'Data & privacy'],
        ['id' => 'credits', 'icon' => 'bi-box-seam', 'label' => 'Third-party & credits'],
        ['id' => 'version-history', 'icon' => 'bi-clock-history', 'label' => 'Version history'],
        ['id' => 'faq', 'icon' => 'bi-wrench', 'label' => 'Troubleshooting & FAQ'],
    ];

    /**
     * One card per tool: named route, icon, one-line purpose and the
     * capabilities that are actually implemented.
     *
     * @var list<array{key: string, label: string, icon: string, route: string, summary: string, points: list<string>}>
     */
    protected const TOOLS = [
        [
            'key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid-1x2-fill', 'route' => 'home',
            'summary' => 'Launch pad with live counters and shortcut tiles.',
            'points' => [
                'Counters for notes, open todo items, upcoming events and imported tracks.',
                'Shortcut tiles open the tools you use most.',
                'Which counters and tiles appear is chosen in Settings › Dashboard.',
            ],
        ],
        [
            'key' => 'notes', 'label' => 'Notes', 'icon' => 'bi-journal-text', 'route' => 'notes.index',
            'summary' => 'Free-form notes with a rich-text body.',
            'points' => [
                'Toolbar formatting: headings, bold, italic, underline, strikethrough, lists, alignment, inline code and links.',
                'Pasted HTML is reduced to a safe allow-list of tags and attributes before it is saved, on both the client and the server.',
                'List order comes from Settings › Notes — newest, oldest or by title.',
                'Deleting a note sends it to the Recycle Bin, where it stays restorable.',
            ],
        ],
        [
            'key' => 'todos', 'label' => 'Todo Lists', 'icon' => 'bi-list-check', 'route' => 'todos.index',
            'summary' => 'Checklists with progress tracking.',
            'points' => [
                'Items are added while you type; Enter opens the next row.',
                'Ticking an item saves immediately and moves the progress bar.',
                'Optional due date, shown as overdue once the day has passed, plus a priority label.',
                'A description carries the context of the whole list.',
            ],
        ],
        [
            'key' => 'calendar', 'label' => 'Calendar', 'icon' => 'bi-calendar-event-fill', 'route' => 'calendar.index',
            'summary' => 'Month grid combining your events with public holidays.',
            'points' => [
                'Events have a title, a start date and time, an optional description and an optional address.',
                'A map picker stores latitude and longitude for the event; saved points link out to OpenStreetMap.',
                'Holidays for the visible month are looked up on the Jalali calendar and cached.',
                'Week start — Sunday, Monday or Saturday — is a Calendar preference.',
            ],
        ],
        [
            'key' => 'music', 'label' => 'Music', 'icon' => 'bi-music-note-beamed', 'route' => 'music.index',
            'summary' => 'Local audio library with a player that survives navigation.',
            'points' => [
                'Import files or whole folders; mp3, wav, flac, ogg and aac are copied into storage.',
                'ID3 title, artist, album, genre, year, track number and duration are read on import, and embedded artwork becomes a cover image.',
                'Mini player on every page, full deck on the Music page; playback continues across navigation.',
                'Shuffle and repeat off / all / one, plus a details editor that writes the tags back into the MP3.',
                'Audio is streamed with HTTP range requests, so seeking inside long files is instant.',
            ],
        ],
        [
            'key' => 'calculator', 'label' => 'Calculator', 'icon' => 'bi-calculator-fill', 'route' => 'calculator',
            'summary' => 'Four modes behind one tab strip.',
            'points' => [
                'Standard and Scientific share a tokenizer plus shunting-yard evaluator — no eval().',
                'Scientific adds trigonometry, logarithms, roots, powers, factorial, modulo, parentheses, π and e, with a degree/radian switch.',
                'History keeps the last entries and can be cleared.',
                'BMI in metric or imperial, including the healthy weight range.',
                'Date difference between two days in years, months, days, weeks and total days.',
            ],
        ],
        [
            'key' => 'clock', 'label' => 'Clock', 'icon' => 'bi-clock-fill', 'route' => 'clock',
            'summary' => 'Four tabs driven by one ticking loop.',
            'points' => [
                'Analog and digital local clock, 24- or 12-hour, seconds optional.',
                'Pinned world clocks, searchable across every IANA timezone.',
                'Stopwatch with laps.',
                'Countdown timer with a progress bar, one-minute to one-hour presets and an audible alert.',
                'Repeating alarms that ring while the Clock tool is open.',
            ],
        ],
        [
            'key' => 'converter', 'label' => 'Converter', 'icon' => 'bi-arrow-left-right', 'route' => 'converter',
            'summary' => 'Unit converter across eleven categories.',
            'points' => [
                'Length, mass, temperature, area, volume, speed, time, data, pressure, energy and angle.',
                'Every unit is defined as a factor of the category base; temperature routes through Celsius.',
                'Swap flips source and target; decimal precision comes from Settings › Converter.',
            ],
        ],
        [
            'key' => 'weather', 'label' => 'Weather', 'icon' => 'bi-cloud-sun-fill', 'route' => 'weather.index',
            'summary' => 'Live conditions for the cities you save.',
            'points' => [
                'Saved cities with one home town pinned; add a place by name or use device geolocation.',
                'Current conditions, feels-like, humidity, pressure, visibility, wind speed, direction and gusts, cloud cover and sunrise/sunset in the city\'s own timezone.',
                'Metric or imperial from Settings › Weather.',
                'Each city keeps its last reading, so the dashboard still shows data when a fetch fails.',
            ],
        ],
        [
            'key' => 'bin', 'label' => 'Recycle Bin', 'icon' => 'bi-trash3', 'route' => 'bin.index',
            'summary' => 'Everything you deleted, in one place.',
            'points' => [
                'Notes, todo lists, calendar events, saved cities and tracks are soft-deleted, not dropped.',
                'Restore brings a record back; deleting forever also removes its audio and cover files.',
                'Emptying the bin clears every trashed record at once.',
            ],
        ],
        [
            'key' => 'settings', 'label' => 'Settings', 'icon' => 'bi-gear', 'route' => 'settings',
            'summary' => 'Preferences grouped one tab per tool.',
            'points' => [
                'Tabs: General, Dashboard, Notes, Todo Lists, Calendar, Music, Weather, Clock, Converter.',
                'Appearance covers light, dark and system, applied before first paint.',
                'Every tool header links straight to its own tab.',
            ],
        ],
    ];

    /**
     * Bundled packages and the external services the app talks to.
     *
     * @return list<array{name: string, role: string, icon: string, version: string}>
     */
    protected function credits(array $composer, array $npm): array
    {
        return [
            ['name' => 'Laravel', 'role' => 'Application framework and routing', 'icon' => 'bi-filetype-php', 'version' => app()->version()],
            ['name' => 'PHP', 'role' => 'Runtime hosting the app locally', 'icon' => 'bi-cpu', 'version' => PHP_VERSION],
            ['name' => 'NativePHP (Electron)', 'role' => 'Desktop shell and native file dialogs', 'icon' => 'bi-window-desktop', 'version' => $this->constraint($composer, 'require', 'nativephp/electron')],
            ['name' => 'Tailwind CSS', 'role' => 'Utility styling and the glass design system', 'icon' => 'bi-brush', 'version' => $this->constraint($npm, 'devDependencies', 'tailwindcss')],
            ['name' => 'Hotwire Turbo', 'role' => 'Instant page swaps without stopping playback', 'icon' => 'bi-lightning-charge', 'version' => $this->constraint($npm, 'dependencies', '@hotwired/turbo')],
            ['name' => 'Bootstrap Icons', 'role' => 'Icon set used across the interface', 'icon' => 'bi-collection', 'version' => $this->constraint($npm, 'dependencies', 'bootstrap-icons')],
            ['name' => 'Leaflet', 'role' => 'Map picker for calendar events', 'icon' => 'bi-geo-alt', 'version' => $this->constraint($npm, 'dependencies', 'leaflet')],
            ['name' => 'getID3', 'role' => 'Audio metadata reading and MP3 tag writing', 'icon' => 'bi-music-note-list', 'version' => $this->constraint($composer, 'require', 'james-heinrich/getid3')],
            ['name' => 'morilog/jalali', 'role' => 'Jalali date maths behind the holiday lookup', 'icon' => 'bi-calendar3', 'version' => $this->constraint($composer, 'require', 'morilog/jalali')],
            ['name' => 'OpenWeatherMap', 'role' => 'Current-conditions data, called server-side', 'icon' => 'bi-cloud-sun', 'version' => 'Web API'],
            ['name' => 'pnldev.com calendar API', 'role' => 'Public holidays for the visible month', 'icon' => 'bi-calendar-event', 'version' => 'Web API'],
            ['name' => 'OpenStreetMap', 'role' => 'Map tiles fetched by the webview', 'icon' => 'bi-map', 'version' => 'Raster tiles'],
        ];
    }

    /**
     * Display application information, capabilities and version history.
     */
    public function index(): View
    {
        $composer = $this->manifest(base_path('composer.json'));
        $npm = $this->manifest(base_path('package.json'));

        $deeplink = config('nativephp.deeplink_scheme');
        $licence = $this->licence($composer);

        return view('about.index', [
            'version' => config('app.version'),
            'changelog' => config('changelog.entries'),
            'sections' => static::SECTIONS,
            'tools' => static::TOOLS,
            'credits' => $this->credits($composer, $npm),
            'licence' => $licence,
            'facts' => [
                ['icon' => 'bi-tag-fill', 'label' => 'App version', 'value' => (string) config('app.version')],
                ['icon' => 'bi-cpu', 'label' => 'Runtime', 'value' => 'PHP '.PHP_VERSION],
                ['icon' => 'bi-filetype-php', 'label' => 'Framework', 'value' => 'Laravel '.app()->version()],
                ['icon' => 'bi-brush', 'label' => 'Styling', 'value' => 'Tailwind CSS '.$this->constraint($npm, 'devDependencies', 'tailwindcss')],
                ['icon' => 'bi-lightning-charge', 'label' => 'Navigation', 'value' => 'Turbo '.$this->constraint($npm, 'dependencies', '@hotwired/turbo')],
                ['icon' => 'bi-link-45deg', 'label' => 'Served at', 'value' => (string) config('app.url'), 'note' => 'Local HTTP only — the app is never hosted remotely.'],
                ['icon' => 'bi-boxes', 'label' => 'Bundle id', 'value' => (string) config('nativephp.app_id')],
                ['icon' => 'bi-signpost-split', 'label' => 'Deep links', 'value' => filled($deeplink) ? $deeplink.'://…' : 'Not registered'],
                ['icon' => 'bi-patch-check', 'label' => 'Licence', 'value' => $licence],
            ],
        ]);
    }

    /**
     * SPDX licence declared in composer.json, falling back to a plain word.
     *
     * @param  array<string, mixed>  $composer
     */
    protected function licence(array $composer): string
    {
        $licence = data_get($composer, 'license');

        return is_array($licence) ? (string) ($licence[0] ?? 'proprietary') : (string) ($licence ?? 'proprietary');
    }

    /**
     * Version constraint from a manifest, without the leading ^ or ~.
     *
     * @param  array<string, mixed>  $manifest
     */
    protected function constraint(array $manifest, string $section, string $package): string
    {
        return ltrim((string) data_get($manifest, "{$section}.{$package}"), '^~ ');
    }

    /**
     * Decode a JSON manifest; an unreadable file simply yields no versions.
     */
    protected function manifest(string $path): array
    {
        if (! is_readable($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
