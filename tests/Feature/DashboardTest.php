<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The dashboard renders for guests (the app is single-user).
     */
    public function test_dashboard_renders(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Dashboard');
    }

    /**
     * Every static page responds successfully.
     */
    public function test_calculator_and_settings_pages_render(): void
    {
        $this->get(route('calculator'))->assertOk();
        $this->get(route('settings'))->assertOk();
        $this->get(route('bin.index'))->assertOk();
        $this->get(route('weather.cities.index'))->assertOk();
    }
}
