<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Term normalisation and matching for the search boxes.
 *
 * Matching runs on decoded PHP values instead of SQL: note bodies are HTML,
 * checklist items are JSON whose Persian text SQLite stores as \uXXXX
 * escapes, and a single-user desktop library is small enough to scan.
 */
class Search
{
    /**
     * The term to search for, normalised, or null when the box is empty.
     */
    public static function term(mixed $raw): ?string
    {
        $term = trim((string) preg_replace('/\s+/u', ' ', (string) $raw));

        return $term === '' ? null : mb_strtolower($term);
    }

    /**
     * Whether any of a row's searchable fields contains the term.
     *
     * @param  array<int, string|int|null>  $fields
     */
    public static function matches(array $fields, ?string $term): bool
    {
        if ($term === null) {
            return true;
        }

        foreach ($fields as $field) {
            if (filled($field) && str_contains(mb_strtolower((string) $field), $term)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rows matching the term, in the order they arrived.
     *
     * Each model declares its own haystack through `searchFields()`, so the
     * page search and the global search can never disagree about what a
     * module contains.
     *
     * @param  Collection<int, object>  $rows
     * @return Collection<int, object>
     */
    public static function filter(Collection $rows, ?string $term): Collection
    {
        return $term === null
            ? $rows->values()
            : $rows->filter(fn (object $row): bool => self::matches($row->searchFields(), $term))->values();
    }
}
