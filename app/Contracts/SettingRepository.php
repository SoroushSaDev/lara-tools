<?php

namespace App\Contracts;

/**
 * Contract for persisting small application preferences.
 */
interface SettingRepository
{
    /**
     * Retrieve a setting value.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Create or update a setting value.
     */
    public function set(string $key, mixed $value): void;

    /**
     * Remove a setting.
     */
    public function forget(string $key): void;
}
