<?php

namespace App\Services;

use App\Contracts\HolidayProvider;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Holiday provider backed by the pnldev.com Iranian calendar API.
 *
 * Responses are cached for six hours so the calendar renders instantly
 * and keeps working offline with previously cached data. A host that never
 * answers is remembered for a few minutes, because otherwise every calendar
 * view would pay the full timeout again for data it cannot get.
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
     * How long an unreachable API is remembered, in seconds.
     *
     * @var int
     */
    protected const UNREACHABLE_TTL = 300;

    /**
     * Cache key marking the API as recently unreachable.
     *
     * @var string
     */
    protected const UNREACHABLE_KEY = 'holidays:unreachable';

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

        if ($pending === []) {
            return $holidays;
        }

        if (Cache::has(self::UNREACHABLE_KEY)) {
            return $holidays + array_fill_keys(array_keys($pending), []);
        }

        $responses = Http::connectTimeout(3)->timeout(8)->pool(fn (Pool $pool) => collect($pending)
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
                $holidays[$key] = [];

                continue;
            }

            $titles = $this->parse($response->json());

            Cache::put("holidays:{$key}", $titles, self::CACHE_TTL);

            $holidays[$key] = $titles;
        }

        // Only a wholly silent host counts: a partial failure is far more likely
        // one bad date than a missing connection. The mark is allowed to expire
        // on its own rather than being cleared here, because reaching this line
        // already means it had expired or was never set.
        if ($failed !== [] && count($failed) === count($pending)) {
            Cache::put(self::UNREACHABLE_KEY, true, self::UNREACHABLE_TTL);

            Log::info('Holiday API unreachable', ['dates' => $failed]);
        }

        return $holidays;
    }

    /**
     * {@inheritDoc}
     */
    public function isUnavailable(): bool
    {
        return Cache::has(self::UNREACHABLE_KEY);
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
