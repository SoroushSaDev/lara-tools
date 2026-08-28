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
