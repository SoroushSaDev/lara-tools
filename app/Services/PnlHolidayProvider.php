<?php

namespace App\Services;

use App\Contracts\HolidayProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Holiday provider backed by the pnldev.com Iranian calendar API.
 *
 * Responses are cached for six hours so the calendar renders instantly
 * and keeps working offline with previously cached data.
 */
class PnlHolidayProvider implements HolidayProvider
{
    /**
     * API endpoint.
     *
     * @var string
     */
    protected const ENDPOINT = 'https://pnldev.com/api/calender';

    /**
     * Cache TTL in seconds.
     *
     * @var int
     */
    protected const CACHE_TTL = 21600;

    /**
     * {@inheritDoc}
     */
    public function forDate(int $year, int $month, int $day): array
    {
        return Cache::remember(
            "holidays:{$year}-{$month}-{$day}",
            self::CACHE_TTL,
            fn () => $this->fetch($year, $month, $day)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function forBatch(array $dates): array
    {
        $holidays = [];
        $pending = [];

        foreach ($dates as [$year, $month, $day]) {
            $key = "{$year}-{$month}-{$day}";
            $cached = Cache::get("holidays:{$key}");

            if ($cached !== null) {
                $holidays[$key] = $cached;

                continue;
            }

            $pending[$key] = [$year, $month, $day];
        }

        if ($pending !== []) {
            $responses = Http::timeout(8)->pool(fn (Pool $pool) => collect($pending)
                ->map(fn (array $date, string $key) => $pool->as($key)->get(self::ENDPOINT, [
                    'year' => $date[0],
                    'month' => $date[1],
                    'day' => $date[2],
                    'holiday' => 'false',
                ]))
                ->all());

            $failed = [];

            foreach ($responses as $key => $response) {
                // A pooled request that never connected yields the exception
                // itself, not a response, so it has no json() to call.
                if (! $response instanceof Response) {
                    $failed[] = $key;

                    // Left uncached on purpose: the next month view retries
                    // instead of showing a blank calendar for the whole TTL.
                    $holidays[$key] = [];

                    continue;
                }

                $titles = $this->parse($response->json());

                Cache::put("holidays:{$key}", $titles, self::CACHE_TTL);

                $holidays[$key] = $titles;
            }

            if ($failed !== []) {
                Log::info('Holiday API unreachable', ['dates' => $failed]);
            }
        }

        return $holidays;
    }

    /**
     * Call the remote API, returning an empty list on any failure.
     *
     * @return array<int, string>
     */
    protected function fetch(int $year, int $month, int $day): array
    {
        try {
            $payload = Http::timeout(8)
                ->get(self::ENDPOINT, [
                    'year' => $year,
                    'month' => $month,
                    'day' => $day,
                    'holiday' => 'false',
                ])
                ->json();
        } catch (ConnectionException $exception) {
            Log::info('Holiday API unreachable', ['exception' => $exception->getMessage()]);

            return [];
        }

        return $this->parse($payload);
    }

    /**
     * Normalise an API payload into a plain list of holiday titles.
     *
     * @return array<int, string>
     */
    protected function parse(mixed $payload): array
    {
        if (! is_array($payload) || ($payload['status'] ?? false) !== true) {
            return [];
        }

        return collect($payload['result']['event'] ?? [])
            ->filter(fn ($event) => filled($event))
            ->map(fn ($event) => (string) $event)
            ->values()
            ->all();
    }
}
