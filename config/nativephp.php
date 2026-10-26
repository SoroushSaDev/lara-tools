<?php

return [
    /**
     * The version of your app.
     * It is used to determine if the app needs to be updated.
     * Increment this value every time you release a new version of your app.
     */
    'version' => env('NATIVEPHP_APP_VERSION', '1.0.0'),

    /**
     * The ID of your application. This should be a unique identifier
     * usually in the form of a reverse domain name.
     * For example: com.nativephp.app
     */
    'app_id' => env('NATIVEPHP_APP_ID', 'com.laratools.desktop'),

    /**
     * If your application allows deep linking, you can specify the scheme
     * to use here. This is the scheme that will be used to open your
     * application from within other applications.
     * For example: "nativephp"
     *
     * This would allow you to open your application using a URL like:
     * nativephp://some/path
     */
    'deeplink_scheme' => env('NATIVEPHP_DEEPLINK_SCHEME', 'laratools'),

    /**
     * The author of your application.
     */
    'author' => env('NATIVEPHP_APP_AUTHOR', 'LaraTools'),

    /**
     * The copyright notice for your application.
     */
    'copyright' => env('NATIVEPHP_APP_COPYRIGHT', 'Copyright © 2026 LaraTools'),

    /**
     * Blurb and homepage written into the packaged app's metadata
     * (package.json, then the NSIS installer and the Linux .desktop entry).
     * Left null, electron-builder fills these with its own placeholders.
     */
    'description' => env('NATIVEPHP_APP_DESCRIPTION', 'Notes, todos, calendar, music, weather and utilities in one offline desktop app.'),

    'website' => env('NATIVEPHP_APP_WEBSITE', 'https://github.com/SoroushSaDev/lara-tools'),

    /**
     * The default service provider for your application. This provider
     * takes care of bootstrapping your application and configuring
     * any global hotkeys, menus, windows, etc.
     */
    'provider' => \App\Providers\NativeAppServiceProvider::class,

    /**
     * A list of environment keys that should be removed from the
     * .env file when the application is bundled for production.
     * You may use wildcards to match multiple keys.
     *
     * Anything not listed here is copied into the installer verbatim,
     * so every secret the app can read from .env belongs on this list.
     *
     * APP_ENV and APP_DEBUG are stripped so a bundle built from a machine
     * with debugging on still ships a production-configured app: without a
     * value, Laravel falls back to production with debugging off.
     */
    'cleanup_env_keys' => [
        'APP_ENV',
        'APP_DEBUG',
        'AWS_*',
        'GITHUB_*',
        'DO_SPACES_*',
        '*_SECRET',
        'ZEPHPYR_*',
        'OPENWEATHER_API_KEY',

        'NATIVEPHP_UPDATER_PATH',
        'NATIVEPHP_APPLE_ID',
        'NATIVEPHP_APPLE_ID_PASS',
        'NATIVEPHP_APPLE_TEAM_ID',
    ],

    /**
     * A list of files and folders that should be removed from the
     * final app before it is bundled for production.
     * You may use glob / wildcard patterns here.
     */
    'cleanup_exclude_files' => [
        'build',
        'temp',
        'content',
        'node_modules',
        '*/tests',
    ],

    /**
     * The NativePHP updater configuration.
     */
    'updater' => [
        /**
         * Whether or not the updater is enabled. Please note that the
         * updater will only work when your application is bundled
         * for production.
         *
         * Off by default: with no Spaces/GitHub credentials configured, an
         * enabled updater spends its release checks failing in the background.
         */
        'enabled' => env('NATIVEPHP_UPDATER_ENABLED', false),

        /**
         * The updater provider to use.
         * Supported: "github", "s3", "spaces"
         */
        'default' => env('NATIVEPHP_UPDATER_PROVIDER', 'spaces'),

        'providers' => [
            'github' => [
                'driver' => 'github',
                'repo' => env('GITHUB_REPO'),
                'owner' => env('GITHUB_OWNER'),
                'token' => env('GITHUB_TOKEN'),
                'vPrefixedTagName' => env('GITHUB_V_PREFIXED_TAG_NAME', true),
                'private' => env('GITHUB_PRIVATE', false),
                'channel' => env('GITHUB_CHANNEL', 'latest'),
                'releaseType' => env('GITHUB_RELEASE_TYPE', 'draft'),
            ],

            's3' => [
                'driver' => 's3',
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'region' => env('AWS_DEFAULT_REGION'),
                'bucket' => env('AWS_BUCKET'),
                'endpoint' => env('AWS_ENDPOINT'),
                'path' => env('NATIVEPHP_UPDATER_PATH', null),
            ],

            'spaces' => [
                'driver' => 'spaces',
                'key' => env('DO_SPACES_KEY_ID'),
                'secret' => env('DO_SPACES_SECRET_ACCESS_KEY'),
                'name' => env('DO_SPACES_NAME'),
                'region' => env('DO_SPACES_REGION'),
                'path' => env('NATIVEPHP_UPDATER_PATH', null),
            ],
        ],
    ],

    /**
     * The queue workers that get auto-started on your application start.
     *
     * The app dispatches no jobs, so a worker would only cost an extra PHP
     * process polling the database every second.
     */
    'queue_workers' => [],

    /**
     * Define your own scripts to run before and after the build process.
     *
     * The bundle has no dev server, so the Vite manifest and hashed assets
     * it are built into the installer — without this the packaged app boots
     * to a page whose CSS and JS both 404.
     */
    'prebuild' => [
        'npm run build',
    ],

    'postbuild' => [
        // 'rm -rf public/build',
    ],

    /**
     * Custom PHP binary path.
     */
    'binary_path' => env('NATIVEPHP_PHP_BINARY_PATH', null),
];
