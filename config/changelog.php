<?php

/*
|--------------------------------------------------------------------------
| Changelog
|--------------------------------------------------------------------------
|
| Version history rendered on the About page as a vertical timeline.
| The newest release comes first; `notes` entries appear top to bottom.
|
*/

return [
    'entries' => [
        [
            'version' => '1.2.0',
            'date' => '2026-10-19',
            'notes' => [
                'Notes now use a full rich-text editor: headings, lists, quotes, links and alignment',
                'New Dashboard settings tab: switch statistics and shortcuts on or off, one by one',
                'Todo lists carry a due date and a priority, and can be sorted by either',
                'Calendar spreads the month grid and the event list side by side on wide screens',
                'Events can pin a location on an interactive map',
                'Music library rows gained a tag editor',
                'Seek and volume sliders show a live time/percentage tooltip',
                'Calculator modes: scientific, BMI and date difference',
                'Weather city page surfaces every field the forecast API returns',
                'Every module fills the window instead of hugging a narrow column',
                'World clocks, alarms, converter and settings rearranged into two columns',
                'About page rewritten as a two-column reference with troubleshooting notes',
            ],
        ],
        [
            'version' => '1.1.0',
            'date' => '2026-08-25',
            'notes' => [
                'Seamless navigation — pages swap instantly and music keeps playing',
                'Persistent mini player on every page; full deck on the Music page',
                'New tool: Clock with analog + digital display, stopwatch, timers and world clocks',
                'New tool: Unit Converter covering 11 categories of units',
                'Settings reorganised into per-tool tabs, reachable from every tool header',
                'About page with version timeline (this one!) linked from the sidebar',
                'Calendar weeks can now start on Monday or Sunday',
                'Light/dark/System label shown beside the theme toggle in the sidebar',
            ],
        ],
        [
            'version' => '1.0.0',
            'date' => '2026-08-24',
            'notes' => [
                'Complete redesign: glassmorphism design system with light & dark themes',
                'Dashboard with live counters for notes, todos, events and tracks',
                'Notes with full CRUD and restorable Recycle Bin for every module',
                'Todo lists with per-item completion and live progress bars',
                'Calendar combining your events with public holiday feeds',
                'Music library: native file/folder import, ID3 metadata, artwork, range streaming',
                'Calculator with a safe expression engine and keyboard support',
                'Weather dashboard with saved cities, home-town pinning and geolocation',
            ],
        ],
    ],
];
