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
}
