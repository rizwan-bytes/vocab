<?php

namespace Tests\Feature\Livewire\Vocabulary;

use App\Enums\WordStatus;
use App\Livewire\Vocabulary\Index;
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

    public function test_it_lists_words(): void
    {
        Word::factory()->create(['word' => 'meticulous']);
        Word::factory()->create(['word' => 'ubiquitous']);

        Livewire::test(Index::class)
            ->assertSee('meticulous')
            ->assertSee('ubiquitous');
    }

    public function test_search_filters_by_word(): void
    {
        Word::factory()->create(['word' => 'meticulous']);
        Word::factory()->create(['word' => 'ubiquitous']);

        Livewire::test(Index::class)
            ->set('search', 'metic')
            ->assertSee('meticulous')
            ->assertDontSee('ubiquitous');
    }

    public function test_status_filter_works(): void
    {
        Word::factory()->status(WordStatus::Mastered)->create(['word' => 'meticulous']);
        Word::factory()->status(WordStatus::New)->create(['word' => 'ubiquitous']);

        Livewire::test(Index::class)
            ->set('status', WordStatus::Mastered->value)
            ->assertSee('meticulous')
            ->assertDontSee('ubiquitous');
    }

    public function test_difficulty_filter_works(): void
    {
        Word::factory()->create(['word' => 'meticulous', 'difficulty_level' => 'C1']);
        Word::factory()->create(['word' => 'happy', 'difficulty_level' => 'A2']);

        Livewire::test(Index::class)
            ->set('difficulty', 'C1')
            ->assertSee('meticulous')
            ->assertDontSee('happy');
    }

    public function test_deleting_a_word_removes_it(): void
    {
        $word = Word::factory()->create(['word' => 'meticulous']);

        Livewire::test(Index::class)->call('delete', $word);

        $this->assertDatabaseMissing('words', ['id' => $word->id]);
    }

    public function test_clear_filters_resets_all_filters(): void
    {
        Livewire::test(Index::class)
            ->set('search', 'foo')
            ->set('status', WordStatus::Mastered->value)
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('status', '');
    }

    public function test_empty_state_is_shown_when_no_words_exist(): void
    {
        Livewire::test(Index::class)->assertSee('Your vocabulary is empty');
    }
}
