<?php

namespace Tests\Unit;

use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Missing keys fall back to the provided default.
     */
    public function test_get_returns_default_for_unknown_keys(): void
    {
        $settings = new SettingService;

        $this->assertNull($settings->get('missing'));
        $this->assertSame('fallback', $settings->get('missing', 'fallback'));
    }

    /**
     * Values round-trip through create and update paths.
     */
    public function test_set_creates_then_updates(): void
    {
        $settings = new SettingService;

        $settings->set('appearance', 'dark');
        $this->assertSame('dark', $settings->get('appearance'));

        $settings->set('appearance', 'light');
        $this->assertSame('light', $settings->get('appearance'));
        $this->assertDatabaseCount('settings', 1);
    }

    /**
     * Forgotten keys are removed and defaults apply again.
     */
    public function test_forget_removes_the_value(): void
    {
        $settings = new SettingService;

        $settings->set('home-town', 'Tehran');
        $settings->forget('home-town');

        $this->assertNull($settings->get('home-town'));
        $this->assertDatabaseCount('settings', 0);
    }
}
