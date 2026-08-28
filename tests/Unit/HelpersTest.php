<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    /**
     * Seconds format as m:ss, with an hours segment when large.
     */
    public function test_format_time(): void
    {
        $this->assertSame('0:00', formatTime(0));
        $this->assertSame('0:59', formatTime(59));
        $this->assertSame('3:35', formatTime(215));
        $this->assertSame('1:01:05', formatTime(3665));
        $this->assertSame('--:--', formatTime(null));
        $this->assertSame('--:--', formatTime('abc'));
    }

    /**
     * Weather conditions map to sensible Bootstrap icons.
     */
    public function test_weather_icons(): void
    {
        $this->assertSame('bi-sun-fill', weatherIcon('Clear'));
        $this->assertSame('bi-cloud-rain-heavy-fill', weatherIcon('Rain'));
        $this->assertSame('bi-cloud-lightning-rain-fill', weatherIcon('Thunderstorm'));
        $this->assertSame('bi-cloud-haze2-fill', weatherIcon('Mist'));

        // Unknown conditions fall back to a partly cloudy glyph.
        $this->assertSame('bi-cloud-sun-fill', weatherIcon('Nebula'));
        $this->assertSame('bi-cloud-sun-fill', weatherIcon(null));
    }

    /**
     * Unit symbols follow the configured measurement system.
     */
    public function test_temperature_unit_symbol(): void
    {
        $this->assertSame('°C', temperatureUnitSymbol('metric'));
        $this->assertSame('°F', temperatureUnitSymbol('imperial'));
        $this->assertSame('°C', temperatureUnitSymbol(null));
    }
}
