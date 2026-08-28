<?php

namespace App\Services;

use App\Contracts\SettingRepository;
use App\Models\Setting;

/**
 * Database-backed setting repository.
 *
 * Values are memoised per request; the table is tiny so an additional
 * cache layer would only add failure modes on fresh installations.
 */
class SettingService implements SettingRepository
{
    /**
     * In-memory cache resolved during the current request.
     *
     * @var array<string, mixed>
     */
    protected array $local = [];

    /**
     * Retrieve a setting value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->local)) {
            return $this->local[$key];
        }

        $value = Setting::firstWhere('key', $key)?->value;

        // Only cache real values so misses keep honouring their default.
        if ($value === null) {
            return $default;
        }

        return $this->local[$key] = $value;
    }

    /**
     * Create or update a setting value.
     */
    public function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);

        $this->local[$key] = (string) $value;
    }

    /**
     * Remove a setting.
     */
    public function forget(string $key): void
    {
        Setting::firstWhere('key', $key)?->delete();

        unset($this->local[$key]);
    }
}
