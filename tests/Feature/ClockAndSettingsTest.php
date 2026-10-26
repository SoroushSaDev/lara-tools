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
     * A switch that is off reaches the server as "0", because the toggle
     * component pairs every checkbox with a hidden "0" input.
     */
    public function test_unchecked_switches_persist_as_off(): void
    {
        $this->put(route('settings.update'), [
            'calendar_show_holidays' => '1',
            'music_shuffle_default' => '1',
            'appearance' => 'dark',
            'weather_units' => 'metric',
        ])->assertRedirect();

        $this->put(route('settings.update'), [
            'calendar_show_holidays' => '0',
            'music_shuffle_default' => '0',
            'appearance' => 'dark',
            'weather_units' => 'metric',
        ])->assertRedirect();

        $settings = app(SettingRepository::class);

        $this->assertSame('0', $settings->get('calendar_show_holidays'));
        $this->assertSame('0', $settings->get('music_shuffle_default'));
    }

    /**
     * A payload that omits a switch entirely is not a form submission, so it
     * must leave the stored preference alone rather than clearing it.
     */
    public function test_omitted_switches_keep_their_stored_value(): void
    {
        $this->put(route('settings.update'), [
            'calendar_show_holidays' => '1',
            'music_shuffle_default' => '1',
        ])->assertRedirect();

        $this->put(route('settings.update'), [
            'appearance' => 'dark',
            'notes_sort' => 'newest',
            // Both switches deliberately absent, as a partial payload would.
        ])->assertRedirect();

        $settings = app(SettingRepository::class);

        $this->assertSame('1', $settings->get('calendar_show_holidays'));
        $this->assertSame('1', $settings->get('music_shuffle_default'));
        $this->assertSame('newest', $settings->get('notes_sort'));
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

    /**
     * A text field (the weather API key) is stored exactly as typed, minus
     * the padding a pasted value always seems to carry.
     */
    public function test_text_fields_persist_trimmed(): void
    {
        $this->put(route('settings.update'), [
            'weather_api_key' => '  abc123  ',
        ])->assertRedirect();

        $this->assertSame('abc123', app(SettingRepository::class)->get('weather_api_key'));
    }

    /**
     * Submitting the field empty is the user pressing "clear", so the stored
     * secret goes away; omitting it entirely is not a clear.
     */
    public function test_text_fields_clear_when_submitted_empty(): void
    {
        $this->put(route('settings.update'), ['weather_api_key' => 'secret'])->assertRedirect();

        $this->put(route('settings.update'), [
            'weather_api_key' => '',
            'appearance' => 'dark',
        ])->assertRedirect();

        $this->assertSame('', app(SettingRepository::class)->get('weather_api_key'));

        // A payload without the key leaves the stored one alone.
        $this->put(route('settings.update'), ['weather_api_key' => 'again'])->assertRedirect();
        $this->put(route('settings.update'), ['appearance' => 'dark'])->assertRedirect();

        $this->assertSame('again', app(SettingRepository::class)->get('weather_api_key'));
    }

    /**
     * Text fields are bounded, so an oversized value is refused rather than
     * truncated into a key that will never authenticate.
     */
    public function test_oversized_text_fields_are_rejected(): void
    {
        $this->from(route('settings'))
            ->put(route('settings.update'), ['weather_api_key' => str_repeat('k', 256)])
            ->assertSessionHasErrors('weather_api_key');

        $this->assertNull(app(SettingRepository::class)->get('weather_api_key'));
    }
}
