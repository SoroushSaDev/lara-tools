<?php

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A note can be created and shows up in the library.
     */
    public function test_notes_can_be_created_and_listed(): void
    {
        $this->post(route('notes.store'), [
            'title' => 'Shopping list',
            'body' => 'Milk, eggs, coffee',
        ])->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', ['title' => 'Shopping list']);

        $this->get(route('notes.index'))->assertOk()->assertSee('Shopping list');
    }

    /**
     * Validation rejects an empty body.
     */
    public function test_note_body_is_required(): void
    {
        $this->from(route('notes.create'))
            ->post(route('notes.store'), ['title' => 'No body'])
            ->assertSessionHasErrors('body')
            ->assertRedirect(route('notes.create'));

        $this->assertDatabaseCount('notes', 0);
    }

    /**
     * Deleting a note keeps it restorable from the recycle bin.
     */
    public function test_deleted_notes_land_in_the_bin_and_can_be_restored(): void
    {
        $note = Note::create(['title' => 'Temp', 'body' => 'Body']);

        $this->delete(route('notes.destroy', $note))->assertRedirect();

        $this->assertSoftDeleted($note);
        $this->get(route('bin.index'))->assertOk()->assertSee('Temp');

        $this->post(route('bin.restore', ['notes', $note->id]))->assertRedirect();
        $this->assertDatabaseHas('notes', ['id' => $note->id, 'deleted_at' => null]);
    }

    /**
     * Purging from the bin removes the row for good.
     */
    public function test_bin_purge_deletes_forever(): void
    {
        $note = Note::create(['title' => 'Gone soon', 'body' => 'Body']);
        $note->delete();

        $this->delete(route('bin.destroy', ['notes', $note->id]))->assertRedirect();

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }
}
