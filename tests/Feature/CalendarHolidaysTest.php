<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The holiday list is downloaded per day of the visible month, so an offline
 * machine must neither re-pay the timeout on every view nor look like a month
 * with nothing in it.
 */
class CalendarHolidaysTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    protected function sentCount(): int
    {
        return count(Http::recorded());
    }

    public function test_each_day_of_the_visible_month_is_fetched_once_and_then_cached(): void
    {
        Http::fake([
            'pnldev.com/*' => Http::response(['status' => true, 'result' => ['event' => ['Test holiday']]]),
        ]);

        $this->get(route('calendar.index'))->assertOk()->assertSee('Test holiday');

        $afterFirstView = $this->sentCount();
        $this->assertGreaterThan(28, $afterFirstView);

        // A second view of the same month reads only from cache.
        $this->get(route('calendar.index'))->assertOk()->assertSee('Test holiday');

        $this->assertSame($afterFirstView, $this->sentCount());
    }

    public function test_holidays_render_from_cache_with_no_connection(): void
    {
        Http::fake([
            'pnldev.com/*' => Http::response(['status' => true, 'result' => ['event' => ['Offline-proof holiday']]]),
        ]);

        $this->get(route('calendar.index'))->assertOk()->assertSee('Offline-proof holiday');

        Http::fake(fn () => throw new ConnectionException('Connection refused.'));

        $this->get(route('calendar.index'))
            ->assertOk()
            ->assertSee('Offline-proof holiday')
            ->assertDontSee('Holiday list could not be downloaded');
    }

    public function test_unreachable_api_is_remembered_instead_of_retried_per_visit(): void
    {
        // A fake that throws is not recorded by Http::recorded(), so the
        // attempts are counted where they happen.
        $counter = new \stdClass;
        $counter->attempts = 0;

        Http::fake(function () use ($counter) {
            $counter->attempts++;

            throw new ConnectionException('Connection refused.');
        });

        $this->get(route('calendar.index'))
            ->assertOk()
            ->assertSee('Holiday list could not be downloaded');

        $this->assertGreaterThan(0, $counter->attempts);

        $attemptsAfterFirstVisit = $counter->attempts;

        // Nothing new goes out while the failure is remembered, so an offline
        // calendar opens instantly instead of waiting on another month of timeouts.
        $this->get(route('calendar.index'))->assertOk();

        $this->assertSame($attemptsAfterFirstVisit, $counter->attempts);
    }

    public function test_the_remembered_failure_expires_so_the_calendar_recovers(): void
    {
        // One fake that changes behaviour, since a later Http::fake() does not
        // replace the stub already registered.
        $online = false;

        Http::fake(function () use (&$online) {
            if (! $online) {
                throw new ConnectionException('Connection refused.');
            }

            return Http::response(['status' => true, 'result' => ['event' => ['Recovered']]]);
        });

        $this->get(route('calendar.index'))->assertOk();

        $this->assertTrue(Cache::has('holidays:unreachable'));

        \Illuminate\Support\Carbon::setTestNow(now()->addSeconds(301));

        $online = true;

        $this->get(route('calendar.index'))->assertOk()->assertSee('Recovered');

        $this->assertFalse(Cache::has('holidays:unreachable'));

        \Illuminate\Support\Carbon::setTestNow();
    }
}
