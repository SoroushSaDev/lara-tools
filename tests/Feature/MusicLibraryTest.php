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
     * The like toggle answers with the state it just stored, and stores it.
     */
    public function test_liking_a_track_persists_and_reports_the_new_state(): void
    {
        $music = $this->createTrack();

        $this->patch(route('music.like', $music))
            ->assertOk()
            ->assertJson(['liked' => true]);

        $this->assertTrue($music->fresh()->liked);

        $this->patch(route('music.like', $music))
            ->assertOk()
            ->assertJson(['liked' => false]);

        $this->assertFalse($music->fresh()->liked);
    }

    /**
     * An import starts unliked: the column must not default everyone in.
     */
    public function test_imported_tracks_start_unliked(): void
    {
        $this->assertFalse($this->createTrack()->fresh()->liked);
    }

    /**
     * The Liked filter keeps only the liked rows, on the page and in the
     * frame the search box reloads into.
     */
    public function test_the_library_can_be_filtered_to_liked_tracks(): void
    {
        $kept = $this->createTrack(['title' => 'Keep Me', 'name' => 'keep.mp3']);
        $this->createTrack(['title' => 'Skip Me', 'name' => 'skip.mp3']);

        $this->patch(route('music.like', $kept));

        $this->get(route('music.index'))
            ->assertSee('Keep Me')
            ->assertSee('Skip Me');

        $this->get(route('music.index', ['liked' => 1]))
            ->assertSee('Keep Me')
            ->assertDontSee('Skip Me');

        $this->withHeader('Turbo-Frame', 'music-results')
            ->get(route('music.index', ['liked' => 1]))
            ->assertSee('Keep Me')
            ->assertDontSee('Skip Me');
    }

    /**
     * Filtering and searching narrow together instead of replacing each other.
     */
    public function test_the_liked_filter_combines_with_the_search_term(): void
    {
        foreach (['Alpha Song', 'Beta Song'] as $title) {
            $this->patch(route('music.like', $this->createTrack(['title' => $title])));
        }

        $this->createTrack(['title' => 'Alpha Reject']);

        $this->get(route('music.index', ['liked' => 1, 'q' => 'alpha']))
            ->assertSee('Alpha Song')
            ->assertDontSee('Beta Song')
            // Matching, but unliked: the filter must have removed it first.
            ->assertDontSee('Alpha Reject');
    }

    /**
     * The search box carries the active filter, or typing would drop it.
     */
    public function test_the_search_box_carries_the_liked_filter(): void
    {
        $this->get(route('music.index'))->assertDontSee('name="liked"', false);

        $this->get(route('music.index', ['liked' => 1]))
            ->assertSee('<input type="hidden" name="liked" value="1"/>', false);
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
