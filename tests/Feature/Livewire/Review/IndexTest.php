<?php

namespace Tests\Feature\Livewire\Review;

use App\Livewire\Review\Index;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_successfully(): void
    {
        Livewire::test(Index::class)->assertStatus(200);
    }

    public function test_it_shows_the_empty_state_when_nothing_is_due(): void
    {
        Word::factory()->create(['next_review_at' => now()->addDays(5)]);

        Livewire::test(Index::class)->assertSee('All caught up');
    }

    public function test_it_shows_a_due_word(): void
    {
        Word::factory()->due()->create(['word' => 'meticulous']);

        Livewire::test(Index::class)
            ->assertSee('meticulous')
            ->assertSee('Reveal Answer');
    }

    public function test_the_definition_is_hidden_until_revealed(): void
    {
        Word::factory()->due()->create(['word' => 'meticulous', 'simple_definition' => 'A very unique phrase xyz.']);

        Livewire::test(Index::class)
            ->assertDontSee('A very unique phrase xyz.')
            ->call('reveal')
            ->assertSee('A very unique phrase xyz.');
    }

    public function test_rating_a_word_advances_to_the_next_one(): void
    {
        Word::factory()->due()->create(['word' => 'first-word']);
        Word::factory()->due()->create(['word' => 'second-word']);

        $component = Livewire::test(Index::class)->call('reveal');

        $component->call('rate', 'good')
            ->assertSet('completedCount', 1)
            ->assertSet('revealed', false);
    }

    public function test_rating_the_last_due_word_shows_the_empty_state(): void
    {
        Word::factory()->due()->create();

        Livewire::test(Index::class)
            ->call('reveal')
            ->call('rate', 'good')
            ->assertSee('All caught up')
            ->assertSee('1 word');
    }

    public function test_an_again_rating_removes_the_word_from_the_due_queue(): void
    {
        $word = Word::factory()->due()->create();

        Livewire::test(Index::class)
            ->call('reveal')
            ->call('rate', 'again');

        // Again schedules a short same-day delay, so it should no longer be "due" right now.
        $this->assertFalse(Word::query()->due()->whereKey($word->id)->exists());
    }
}
