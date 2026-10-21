<?php

namespace App\Support;

/**
 * Dashboard ordering preferences, stored as JSON arrays of tile keys.
 *
 * Stored values are always re-normalised against the canonical key list, so a
 * half-written or hand-edited preference falls back to the declared order
 * instead of hiding or duplicating a tile.
 */
class DashboardOrder
{
    /**
     * Resolve a stored order into the exact list of keys to render.
     *
     * Unknown and repeated entries are dropped; keys the user saved before a
     * tile existed are appended, so new tiles never disappear.
     *
     * @param  string|null  $json  Raw setting value.
     * @param  list<string>  $keys  Canonical keys, in declared order.
     * @return list<string>
     */
    public static function decode(?string $json, array $keys): array
    {
        $stored = json_decode((string) $json, true);

        if (! is_array($stored)) {
            return $keys;
        }

        $ordered = array_values(array_unique(array_filter(
            array_map(static fn ($key): string => is_string($key) ? $key : '', $stored),
            static fn (string $key): bool => in_array($key, $keys, true),
        )));

        return [...$ordered, ...array_values(array_diff($keys, $ordered))];
    }

    /**
     * Normalised JSON ready to persist.
     *
     * @param  list<string>  $keys
     */
    public static function encode(?string $json, array $keys): string
    {
        return json_encode(static::decode($json, $keys), JSON_THROW_ON_ERROR);
    }
}
