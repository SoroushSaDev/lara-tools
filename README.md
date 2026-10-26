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

Package the desktop app on the machine matching its target — Electron cannot
cross-compile installers:

```bash
php artisan native:build linux x64 --no-interaction   # AppImage + .deb
php artisan native:build win x64 --no-interaction     # NSIS installer
```

Front-end assets are compiled for you: `nativephp.prebuild` runs
`npm run build` as part of packaging, so the installer always contains a
current `public/build`. The results are written to `dist/`.

Nothing is uploaded anywhere: `-p never` builds locally, and the updater is
off, so an installed app never phones home for release checks. The app's
identity (name, version, appId, deep-link scheme) comes from
`config/nativephp.php`, which reads the `NATIVEPHP_*` keys of `.env`. Only
`.env.example` is committed, so CI builds use the config defaults — keep your
local `.env` aligned with it, or installers built by CI and by hand will get
different app IDs.

`.github/workflows/ci.yml` runs Pint, the test suite on every PHP version
NativePHP can bundle, a Vite build, and a check that no credential is tracked
or staged for shipping. `.github/workflows/release.yml` builds the Linux and
Windows installers and, for a `v*` tag, publishes them as a GitHub release.

## Testing & Code Style

```bash
composer test          # runs the PHPUnit suite against an in-memory database
./vendor/bin/pint      # fixes code style (PSR-12 / Laravel preset)
```

## Configuration Reference

| Variable | Purpose |
| --- | --- |
| `OPENWEATHER_API_KEY` | API key used by the weather module (development fallback; the key typed in Settings wins, and this one is stripped from every installer) |
| `OPENWEATHER_BASE_URL` | Override the OpenWeatherMap endpoint (optional) |
| `DB_CONNECTION` / `DB_DATABASE` | Local database (SQLite by default) |
| `NATIVEPHP_APP_ID` / `NATIVEPHP_APP_VERSION` / `NATIVEPHP_DEEPLINK_SCHEME` | Packaged app identity; defaults live in `config/nativephp.php` |

User preferences (theme, weather units, home town, weather API key) are stored
in the local `settings` table at runtime — no environment changes needed.

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
