<p align="center">
    <span class="flex size-11 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-fuchsia-600"></span>
</p>

<h1 align="center">LaraTools</h1>

<p align="center">
    <strong>A cross-platform desktop companion app built with Laravel, NativePHP and Tailwind CSS.</strong>
</p>

---

## Introduction

LaraTools bundles a curated set of everyday productivity tools into a single,
native desktop application. It runs on the same PHP codebase you would deploy
to the web — packaged for the desktop with [NativePHP](https://nativephp.com)
(Electron) and styled with Tailwind CSS v4.

### Features

| Tool | Highlights |
| --- | --- |
| **Dashboard** | Live counters for notes, open checklist items, upcoming events and tracks |
| **Notes** | Create, edit, browse and search your quick thoughts |
| **Todo Lists** | Checklists with per-item completion, live progress bars and preserved state on edit |
| **Calendar** | Month grid with event dots, holiday & observance feed, full event CRUD |
| **Music Player** | Import files/folders from disk, ID3 metadata + album art extraction, seekable streaming player with shuffle/repeat/volume |
| **Calculator** | Safe expression engine (no `eval`) with correct operator precedence and keyboard support |
| **Weather** | Current conditions per saved city, home-town pinning, geolocation lookup, metric/imperial units |
| **Recycle Bin** | Every delete is restorable until purged — across all modules |
| **Settings** | Light / dark / system theme, temperature units |

## Tech Stack

- [Laravel 12](https://laravel.com) — application framework
- [NativePHP (Electron)](https://nativephp.com) — desktop packaging, native dialogs & menus
- [Tailwind CSS v4](https://tailwindcss.com) + Vite — design system, theming and animations
- [Bootstrap Icons](https://icons.getbootstrap.com) — iconography
- SQLite — local storage (no server required)
- [getID3](https://github.com/JamesHeinrich/getID3) — audio metadata parsing

## Requirements

- PHP >= 8.2 (with `sqlite3`, `fileinfo` and `mbstring` extensions)
- Composer
- Node.js >= 20 and npm

## Installation

### 1. Clone and install dependencies

```bash
git clone <repository-url> lara-tools
cd lara-tools

composer install
npm install
```

### 2. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

Then add your OpenWeatherMap API key (free tier is sufficient):

```dotenv
OPENWEATHER_API_KEY=your_api_key_here
```

> Get a key at [openweathermap.org/api](https://openweathermap.org/api).

### 3. Prepare the database and assets

```bash
touch database/database.sqlite
php artisan migrate
npm run build
```

### Running in development

Run the web server, queue worker, log tail and Vite dev server together:

```bash
composer dev
```

…or run it as a desktop app during development:

```bash
composer native:dev
```

This launches a native Electron window pointing at your local Laravel app,
with hot reloading through Vite.

> **First desktop run only:** the Electron toolchain (Node dependencies and
> the bundled PHP runtime) must be installed once before `native:dev`:
>
> ```bash
> php artisan native:install   # or: php artisan native:serve (without -D) once
> ```
>
> `composer native:dev` starts with `-D`, which skips re-installing those
> dependencies on every launch — much faster and immune to flaky networks.
> If you are behind a restricted network, export
> `ELECTRON_MIRROR=https://npmmirror.com/mirrors/electron/` before running
> `native:install`.

### Building for production

Build the front-end assets first, then package the desktop binary:

```bash
npm run build
php artisan native:build
```

The compiled installer/executable is written to the `dist/` directory.

## Testing & Code Style

```bash
composer test          # runs the PHPUnit suite against an in-memory database
./vendor/bin/pint      # fixes code style (PSR-12 / Laravel preset)
```

## Configuration Reference

| Variable | Purpose |
| --- | --- |
| `OPENWEATHER_API_KEY` | API key used by the weather module |
| `OPENWEATHER_BASE_URL` | Override the OpenWeatherMap endpoint (optional) |
| `DB_CONNECTION` / `DB_DATABASE` | Local database (SQLite by default) |

User preferences (theme, weather units, home town) are stored in the local
`settings` table at runtime — no environment changes needed.

## Project Structure

```
app/
├── Contracts/            # Interfaces: settings, weather, holidays
├── Exceptions/           # Domain exceptions (e.g. WeatherException)
├── Http/
│   ├── Controllers/      # Thin controllers, one per feature
│   └── Requests/         # Form validation rules
├── Models/               # Eloquent models (soft-deletes where restorable)
├── Providers/            # DI bindings + native window/menu bootstrap
└── Services/             # Business logic: weather API, music library,
                          # settings repository, recycle bin, holidays
resources/
├── css/app.css           # Design tokens, dark mode, animations, components
└── js/
    ├── app.js            # Single entry point
    └── modules/          # Feature JS: player, calculator engine, calendar,
                          # todo toggles, theme, toasts, confirm dialogs
```

## License

Released under the [MIT license](LICENSE).
