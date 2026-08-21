<?php

namespace Tests\Feature\Livewire\Tutor;

use App\Livewire\Tutor\VocabularyPractice;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class VocabularyPracticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_successfully(): void
    {
        Livewire::test(VocabularyPractice::class)->assertStatus(200);
    }

    public function test_it_shows_the_empty_state_with_no_words(): void
    {
        Livewire::test(VocabularyPractice::class)->assertSee('have any saved words');
    }

    public function test_it_picks_words_when_some_exist(): void
    {
        Word::factory()->count(3)->create();

        $component = Livewire::test(VocabularyPractice::class);

        $this->assertNotEmpty($component->get('wordIds'));
    }

    public function test_generating_an_exercise_shows_the_instructions(): void
    {
        Word::factory()->count(2)->create();
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode(['instructions' => 'Write about your day.'])]]]]],
            ]),
        ]);

        Livewire::test(VocabularyPractice::class)
            ->call('generateExercise')
            ->assertSet('instructions', 'Write about your day.');
    }

    public function test_submitting_creates_a_session_with_the_practiced_words(): void
    {
        // Set instructions directly rather than also exercising generateExercise()'s own
        // Gemini call here (covered by its own test above) — see the note in
        // SpeakingTest about Http::fake() only honoring the latest stub set per test.
        $words = Word::factory()->count(2)->create();
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode([
                    'summary' => 'x',
                    'word_usage' => $words->map(fn ($w) => [
                        'word' => $w->word, 'used' => true, 'used_correctly' => true, 'feedback' => 'Good.',
                    ])->all(),
                    'overall_feedback' => 'Well done.',
                ])]]]]],
            ]),
        ]);

        Livewire::test(VocabularyPractice::class)
            ->set('wordIds', $words->pluck('id')->all())
            ->set('instructions', 'Write about your day using both words.')
            ->set('submission', 'A submission using both words.')
            ->call('submit')
            ->assertRedirect();

        $this->assertDatabaseHas('tutor_sessions', ['type' => 'vocabulary_practice']);
    }
}
