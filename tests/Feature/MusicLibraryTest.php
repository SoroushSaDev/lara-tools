<?php

namespace Tests\Feature;

use App\Models\Music;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MusicLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected string $audioPath = 'music/testing-sample.mp3';

    protected string $coverPath = 'covers/testing-sample.jpg';

    protected function tearDown(): void
    {
        Storage::disk('public')->delete([$this->audioPath, $this->coverPath]);

        parent::tearDown();
    }

    /**
     * Create a track row backed by a real (tiny) file on the public disk.
     */
    protected function createTrack(array $attributes = []): Music
    {
        Storage::disk('public')->put($this->audioPath, 'ID3 fake audio bytes');

        return Music::create(array_merge([
            'name' => 'testing-sample.mp3',
            'path' => $this->audioPath,
            'duration' => 12,
        ], $attributes));
    }

    /**
     * Tracks stream by ID with range support, whatever their file name is.
     */
    public function test_tracks_stream_by_id(): void
    {
        $music = $this->createTrack([
            // A name that would break any filename-based route constraint.
            'name' => '01. Some Artist - Song (320).mp3',
        ]);

        // Streamed bodies are echoed directly and cannot be asserted in
        // tests; status and range headers are the meaningful contract here.
        $this->get($music->streamUrl())
            ->assertOk()
            ->assertHeader('Accept-Ranges', 'bytes')
            ->assertHeader('Content-Type', 'audio/mpeg');

        // Browsers seek by requesting byte ranges.
        $this->call('GET', $music->streamUrl(), [], [], [], ['HTTP_RANGE' => 'bytes=0-2'])
            ->assertStatus(206)
            ->assertHeader('Content-Range', 'bytes 0-2/20');
    }

    /**
     * A track whose file vanished streams nothing and reports 404.
     */
    public function test_streaming_a_missing_file_returns_404(): void
    {
        $music = Music::create(['name' => 'ghost.mp3', 'path' => 'music/ghost.mp3']);

        $this->get($music->streamUrl())->assertNotFound();
    }

    /**
     * Cover art is served through its own endpoint.
     */
    public function test_cover_art_is_served_by_id(): void
    {
        Storage::disk('public')->put($this->coverPath, "\x89PNG\r\n\x1a\n fake");

        $music = $this->createTrack(['cover_image' => $this->coverPath]);

        // BinaryFileResponse sniffs MIME from real content; a fake byte
        // string may guess differently, so only assert delivery works.
        $this->get($music->coverUrl())->assertOk();
    }

    /**
     * Tracks without artwork return 404 from the cover endpoint.
     */
    public function test_cover_endpoint_returns_404_without_artwork(): void
    {
        $music = $this->createTrack();

        $this->get($music->coverUrl())->assertNotFound();
    }
}
