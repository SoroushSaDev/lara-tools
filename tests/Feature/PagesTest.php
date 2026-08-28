<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Note;
use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every page renders without undefined-variable or route errors,
     * including all create/edit forms with and without a bound model.
     */
    public function test_every_page_renders(): void
    {
        $note = Note::create(['title' => 'Hello', 'body' => 'World']);
        $todo = Todo::create(['title' => 'Errands', 'items' => []]);
        $event = Event::create(['title' => 'Meetup', 'starts_at' => now()->addDay()]);

        $pages = [
            route('home'),
            route('notes.index'),
            route('notes.create'),
            route('notes.show', $note),
            route('notes.edit', $note),
            route('todos.index'),
            route('todos.create'),
            route('todos.show', $todo),
            route('todos.edit', $todo),
            route('calendar.index'),
            route('calendar.create'),
            route('calendar.edit', $event),
            route('music.index'),
            route('calculator'),
            route('clock'),
            route('converter'),
            route('weather.index'),
            route('weather.cities.index'),
            route('settings'),
            route('settings', ['tab' => 'music']),
            route('about'),
            route('bin.index'),
        ];

        foreach ($pages as $page) {
            $this->get($page)->assertOk();
        }
    }

    /**
     * The note editor renders empty when creating (no model bound).
     */
    public function test_note_create_form_has_empty_fields(): void
    {
        $this->get(route('notes.create'))
            ->assertOk()
            ->assertSee('name="title"', false);
    }
}
