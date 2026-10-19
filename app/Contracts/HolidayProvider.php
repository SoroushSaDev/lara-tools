<?php

namespace App\Contracts;

/**
 * Contract for sources of public-holiday information.
 */
interface HolidayProvider
{
    /**
     * Retrieve the holiday descriptions for the given Jalali date.
     *
     * @param  int  $year  Jalali year.
     * @param  int  $month  Jalali month (1-12).
     * @param  int  $day  Jalali day.
     * @return array<int, string>
     */
    public function forDate(int $year, int $month, int $day): array;

    /**
     * Retrieve holidays for many Jalali dates in a single batch.
     *
     * Implementations should serve cache hits without touching the network
     * and fetch the remainder concurrently.
     *
     * @param  array<int, array{0: int, 1: int, 2: int}>  $dates  Each entry is [year, month, day].
     * @return array<string, array<int, string>> Map of "year-month-day" to holiday titles.
     */
    public function forBatch(array $dates): array;
}
