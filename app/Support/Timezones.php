<?php

namespace App\Support;

use DateTimeZone;

/**
 * World-clock timezones.
 *
 * Every identifier PHP ships with is offered, so no city a user looks for is
 * missing; config/clock.php only supplies friendlier labels for the popular
 * ones (Asia/Kolkata is shown as "Mumbai"). Region-less aliases such as
 * Asia/Istanbul are never offered — the canonical zone covers them — but they
 * stay valid so a clock pinned before the switch can still be renamed or
 * unpinned.
 */
class Timezones
{
    /**
     * Full identifier => label map, built once per request.
     *
     * @var array<string, string>|null
     */
    protected static ?array $labels = null;

    /**
     * Every offerable zone, keyed by identifier and labelled by city.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        if (self::$labels !== null) {
            return self::$labels;
        }

        $curated = config('clock.zones', []);
        $labels = [];

        foreach (DateTimeZone::listIdentifiers() as $identifier) {
            $labels[$identifier] = $curated[$identifier] ?? self::derive($identifier);
        }

        return self::$labels = $labels;
    }

    /**
     * Identifiers accepted by validation: everything offerable plus the
     * curated aliases a user may already have pinned.
     *
     * @return list<string>
     */
    public static function identifiers(): array
    {
        return array_values(array_unique([
            ...array_keys(self::labels()),
            ...array_keys(config('clock.zones', [])),
        ]));
    }

    /**
     * Zones grouped by region for an optgroup list, cities sorted by label.
     *
     * @param  list<string>  $except  Identifiers to leave out (already pinned).
     * @return array<string, array<string, string>>
     */
    public static function grouped(array $except = []): array
    {
        $groups = [];

        foreach (self::labels() as $identifier => $label) {
            if (in_array($identifier, $except, true)) {
                continue;
            }

            $groups[self::region($identifier)][$identifier] = $label;
        }

        foreach ($groups as &$cities) {
            asort($cities, SORT_STRING | SORT_FLAG_CASE);
        }

        unset($cities);

        ksort($groups);

        return $groups;
    }

    /**
     * Identifier => label for a chosen set of zones, in the given order.
     *
     * @param  list<string>  $identifiers
     * @return array<string, string>
     */
    public static function names(array $identifiers): array
    {
        $labels = self::labels();

        return array_combine(
            $identifiers,
            array_map(static fn (string $id): string => $labels[$id] ?? self::derive($id), $identifiers),
        );
    }

    /**
     * "Europe/Lisbon" => "Lisbon", the city portion of an identifier.
     */
    protected static function derive(string $identifier): string
    {
        $city = basename(str_replace('\\', '/', $identifier));

        return str_replace('_', ' ', $city);
    }

    /**
     * Region part of an identifier; singleton zones such as UTC group together.
     */
    protected static function region(string $identifier): string
    {
        return str_contains($identifier, '/') ? substr($identifier, 0, strpos($identifier, '/')) : 'Other';
    }
}
