<?php

namespace Tests\Feature\Livewire\Vocabulary;

use App\Livewire\Vocabulary\AddWord;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AddWordTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGeminiOk(array $overrides = []): void
    {
        $data = array_merge([
            'word' => 'meticulous',
            'pronunciation' => '/test/',
            'part_of_speech' => 'adjective',
            'simple_definition' => 'Very careful and precise.',
            'urdu_meaning' => 'محتاط',
            'detailed_definition' => 'Detailed.',
            'examples' => ['An example.'],
            'synonyms' => ['careful'],
            'antonyms' => ['careless'],
            'common_collocations' => ['very meticulous'],
            'ielts_relevance' => 'Relevant.',
            'difficulty_level' => 'C1',
        ], $overrides);

        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode($data)]]]]],
            ]),
        ]);

        config(['gemini.api_key' => 'test-key']);
    }

    public function test_it_renders_successfully(): void
    {
        Livewire::test(AddWord::class)->assertStatus(200);
    }

    public function test_word_is_required(): void
    {
        Livewire::test(AddWord::class)
            ->set('word', '')
            ->call('generate')
            ->assertHasErrors(['word' => 'required']);
    }

    public function test_generating_a_new_word_calls_gemini_and_shows_the_preview(): void
    {
        $this->fakeGeminiOk();

        Livewire::test(AddWord::class)
            ->set('word', 'meticulous')
            ->call('generate')
            ->assertSet('generatedData.word', 'meticulous')
            ->assertSet('errorMessage', null);

        Http::assertSentCount(1);
    }

    public function test_generating_an_already_existing_word_does_not_call_gemini(): void
    {
        $word = Word::factory()->create(['word' => 'meticulous', 'normalized_word' => 'meticulous']);
        Http::fake(); // any call would be a bug — assert none happen

        Livewire::test(AddWord::class)
            ->set('word', 'Meticulous')
            ->call('generate')
            ->assertSet('existingWordId', $word->id)
            ->assertSet('generatedData', null);

        Http::assertNothingSent();
    }

    public function test_a_gemini_failure_shows_an_error_message_not_a_crash(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake(['*generateContent*' => Http::response('', 500)]);

        Livewire::test(AddWord::class)
            ->set('word', 'meticulous')
            ->call('generate')
            ->assertSet('generatedData', null)
            ->assertSet('errorMessage', fn ($message) => filled($message));
    }

    public function test_saving_the_generated_card_persists_the_word_and_redirects(): void
    {
        $this->fakeGeminiOk();

        Livewire::test(AddWord::class)
            ->set('word', 'meticulous')
            ->call('generate')
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('words', ['word' => 'meticulous']);
    }

    public function test_saving_without_generated_data_does_nothing(): void
    {
        Livewire::test(AddWord::class)->call('save');

        $this->assertDatabaseCount('words', 0);
    }

    public function test_discard_resets_the_form(): void
    {
        $this->fakeGeminiOk();

        Livewire::test(AddWord::class)
            ->set('word', 'meticulous')
            ->set('context', 'some context')
            ->call('generate')
            ->call('discard')
            ->assertSet('word', '')
            ->assertSet('context', null)
            ->assertSet('generatedData', null);
    }

    public function test_the_context_field_is_optional(): void
    {
        $this->fakeGeminiOk();

        Livewire::test(AddWord::class)
            ->set('word', 'meticulous')
            ->set('context', '')
            ->call('generate')
            ->assertHasNoErrors();
    }
}
