<?php

namespace Tests\Feature;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Creating a list normalises and stores its items.
     */
    public function test_lists_store_their_items(): void
    {
        $this->post(route('todos.store'), [
            'title' => 'Chores',
            'description' => 'Weekend round-up',
            'items' => ['Dishes', null, 'Laundry', ''],
        ])->assertRedirect();

        $todo = Todo::firstOrFail();

        $this->assertSame('Chores', $todo->title);
        $this->assertCount(2, $todo->items());
        $this->assertFalse($todo->items()[0]['completed']);
    }

    /**
     * Editing a list must not wipe existing completion state.
     */
    public function test_updating_a_list_preserves_completed_items(): void
    {
        $todo = Todo::create([
            'title' => 'Errands',
            'items' => [
                ['text' => 'Post letter', 'completed' => true],
                ['text' => 'Buy milk', 'completed' => false],
            ],
        ]);

        $this->put(route('todos.update', $todo), [
            'title' => 'Errands (edited)',
            'items' => ['Post letter', 'Buy milk', 'Call dentist'],
        ])->assertRedirect();

        $items = collect($todo->fresh()->items())->pluck('completed', 'text');

        $this->assertTrue($items['Post letter']);
        $this->assertFalse($items['Buy milk']);
        $this->assertFalse($items['Call dentist']);
    }

    /**
     * The toggle endpoint flips a single item and reports progress.
     */
    public function test_items_can_be_toggled(): void
    {
        $todo = Todo::create([
            'title' => 'Pack',
            'items' => [
                ['text' => 'Passport', 'completed' => false],
                ['text' => 'Charger', 'completed' => false],
            ],
        ]);

        $this->patchJson(route('todos.items.toggle', [$todo, 0]))
            ->assertOk()
            ->assertJsonPath('completed', true)
            ->assertJsonPath('progress.completed', 1);

        $this->assertTrue($todo->fresh()->items()[0]['completed']);

        // Toggling again reverts the state.
        $this->patchJson(route('todos.items.toggle', [$todo, 0]))
            ->assertOk()
            ->assertJsonPath('completed', false);
    }

    /**
     * Unknown item indexes are rejected cleanly.
     */
    public function test_toggling_an_unknown_item_fails(): void
    {
        $todo = Todo::create(['title' => 'Empty', 'items' => []]);

        $this->patchJson(route('todos.items.toggle', [$todo, 5]))->assertNotFound();
    }
}
