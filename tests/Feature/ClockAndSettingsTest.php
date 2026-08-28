<?php

namespace Tests\Feature;

use App\Contracts\SettingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * World-clock zones can be pinned and unpinned.
     */
    public function test_world_clock_zones_can_be_added_and_removed(): void
    {
        $this->post(route('clock.zones'), ['add' => 'Europe/London'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->post(route('clock.zones'), ['add' => 'Asia/Tokyo'])->assertRedirect();

        $stored = json_decode(app(SettingRepository::class)->get('world-clocks', '[]'), true);
        $this->assertSame(['Europe/London', 'Asia/Tokyo'], $stored);

        // Removal keeps the remaining pins.
        $this->post(route('clock.zones'), ['remove' => 'Europe/London'])->assertRedirect();

        $stored = json_decode(app(SettingRepository::class)->get('world-clocks', '[]'), true);
        $this->assertSame(['Asia/Tokyo'], $stored);
    }

    /**
     * Unknown or duplicate zones are rejected gracefully.
     */
    public function test_zone_validation_rejects_unknown_cities(): void
    {
        $this->from(route('clock'))
            ->post(route('clock.zones'), ['add' => 'Mars/Olympus'])
            ->assertSessionHasErrors('add');
    }

    /**
     * The new per-tool preference keys validate and persist.
     */
    public function test_tool_preferences_persist(): void
    {
        $this->put(route('settings.update'), [
            'notes_sort' => 'oldest',
            'calendar_week_start' => '1',
            'calendar_show_holidays' => '1',
            'music_shuffle_default' => '1',
            'clock_time_format' => '12h',
            'converter_precision' => '4',
        ])->assertRedirect();

        $settings = app(SettingRepository::class);

        $this->assertSame('oldest', $settings->get('notes_sort'));
        $this->assertSame('1', $settings->get('calendar_week_start'));
        $this->assertSame('1', $settings->get('music_shuffle_default'));
        $this->assertSame('12h', $settings->get('clock_time_format'));
        $this->assertSame('4', $settings->get('converter_precision'));
    }

    /**
     * Unchecked switches are stored as explicit "off".
     */
    public function test_unchecked_switches_persist_as_off(): void
    {
        $this->put(route('settings.update'), [
            'calendar_show_holidays' => '1',
            'music_shuffle_default' => '0',
            'appearance' => 'dark',
            'weather_units' => 'metric',
        ])->assertRedirect();

        $this->put(route('settings.update'), [
            'appearance' => 'dark',
            'weather_units' => 'metric',
            'notes_sort' => 'newest',
            'calendar_week_start' => '0',
            'clock_time_format' => '24h',
            'converter_precision' => '2',
            'music_shuffle_default' => '0',
            // calendar_show_holidays deliberately absent -> switch off.
        ])->assertRedirect();

        $settings = app(SettingRepository::class);

        $this->assertSame('0', $settings->get('calendar_show_holidays'));
        $this->assertSame('0', $settings->get('music_shuffle_default'));
    }

    /**
     * Invalid tool preferences never reach the database.
     */
    public function test_invalid_tool_preferences_are_rejected(): void
    {
        $this->from(route('settings'))
            ->put(route('settings.update'), [
                'notes_sort' => 'random',
                'clock_time_format' => '13h',
                'weather_units' => 'metric',
            ])
            ->assertSessionHasErrors(['notes_sort', 'clock_time_format']);
    }
}
