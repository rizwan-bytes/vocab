<?php

namespace Tests\Feature\Livewire\Vocabulary;

use App\Livewire\Vocabulary\Show;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_successfully(): void
    {
        $word = Word::factory()->create();

        Livewire::test(Show::class, ['word' => $word])->assertStatus(200);
    }

    public function test_it_displays_the_words_data(): void
    {
        $word = Word::factory()->create(['word' => 'meticulous', 'simple_definition' => 'Very careful.']);

        Livewire::test(Show::class, ['word' => $word])
            ->assertSee('meticulous')
            ->assertSee('Very careful.');
    }

    public function test_editing_updates_the_word(): void
    {
        $word = Word::factory()->create();

        Livewire::test(Show::class, ['word' => $word])
            ->call('startEditing')
            ->set('simple_definition', 'A brand new definition.')
            ->call('save')
            ->assertSet('editing', false);

        $this->assertDatabaseHas('words', [
            'id' => $word->id,
            'simple_definition' => 'A brand new definition.',
        ]);
    }

    public function test_simple_definition_is_required_when_saving(): void
    {
        $word = Word::factory()->create();

        Livewire::test(Show::class, ['word' => $word])
            ->call('startEditing')
            ->set('simple_definition', '')
            ->call('save')
            ->assertHasErrors(['simple_definition' => 'required']);
    }

    public function test_deleting_removes_the_word_and_redirects(): void
    {
        $word = Word::factory()->create();

        Livewire::test(Show::class, ['word' => $word])
            ->call('delete')
            ->assertRedirect(route('vocabulary.index'));

        $this->assertDatabaseMissing('words', ['id' => $word->id]);
    }
}
