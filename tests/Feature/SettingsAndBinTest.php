<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAndBinTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Preferences are validated and persisted.
     */
    public function test_settings_can_be_saved(): void
    {
        $this->put(route('settings.update'), [
            'appearance' => 'light',
            'weather_units' => 'imperial',
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'appearance', 'value' => 'light']);
        $this->assertDatabaseHas('settings', ['key' => 'weather_units', 'value' => 'imperial']);
    }

    /**
     * Invalid preference values never reach the database.
     */
    public function test_settings_validation_rejects_unknown_values(): void
    {
        $this->from(route('settings'))
            ->put(route('settings.update'), [
                'appearance' => 'neon',
                'weather_units' => 'kelvin',
                'notes_sort' => 'newest',
                'calendar_week_start' => '0',
                'clock_time_format' => '24h',
                'converter_precision' => '2',
            ])
            ->assertSessionHasErrors(['appearance', 'weather_units']);
    }

    /**
     * Emptying the bin permanently removes every trashed record.
     */
    public function test_emptying_the_bin_purges_all_types(): void
    {
        Event::create(['title' => 'Old meetup', 'starts_at' => now()->addDay()])->delete();
        \App\Models\Note::create(['title' => null, 'body' => 'scratch'])->delete();

        $this->post(route('bin.empty'))->assertRedirect(route('bin.index'));

        $this->assertDatabaseCount('events', 0);
        $this->assertDatabaseCount('notes', 0);
    }
}
