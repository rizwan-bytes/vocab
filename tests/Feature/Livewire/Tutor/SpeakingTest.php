<?php

namespace Tests\Feature\Livewire\Tutor;

use App\Livewire\Tutor\Speaking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SpeakingTest extends TestCase
{
    use RefreshDatabase;

    private function fakeQuestion(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode(['question' => 'Do you enjoy your job?'])]]]]],
            ]),
        ]);
    }

    private function fakeGrading(): array
    {
        return [
            'band_overall' => 6.0,
            'fluency_coherence' => ['score' => 6.0, 'comment' => 'x'],
            'lexical_resource' => ['score' => 6.0, 'comment' => 'x'],
            'grammar' => ['score' => 6.0, 'comment' => 'x'],
            'summary' => 'x',
            'strengths' => [],
            'improvements' => [],
            'vocabulary_suggestions' => [],
        ];
    }

    public function test_it_renders_successfully(): void
    {
        Livewire::test(Speaking::class)->assertStatus(200);
    }

    public function test_getting_a_question_shows_it(): void
    {
        $this->fakeQuestion();

        Livewire::test(Speaking::class)
            ->call('getQuestion')
            ->assertSet('question', 'Do you enjoy your job?');
    }

    public function test_changing_part_resets_the_question(): void
    {
        $this->fakeQuestion();

        Livewire::test(Speaking::class)
            ->call('getQuestion')
            ->call('changePart', 'part2')
            ->assertSet('question', null)
            ->assertSet('part', 'part2');
    }

    public function test_submitting_a_response_creates_a_session_and_redirects(): void
    {
        // Set the question directly rather than also exercising getQuestion()'s own
        // Gemini call here — that flow already has its own test above, and Http::fake()
        // only honors the most recently registered stub set for the whole test, so
        // faking a question response and then re-faking a different grading response
        // in the same test doesn't reliably swap mid-test.
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode($this->fakeGrading())]]]]],
            ]),
        ]);

        Livewire::test(Speaking::class)
            ->set('question', 'Do you enjoy your job?')
            ->set('response', 'Yes, I really enjoy my job because it is challenging.')
            ->call('submit')
            ->assertRedirect();

        $this->assertDatabaseHas('tutor_sessions', ['type' => 'speaking', 'band_overall' => 6.0]);
    }

    public function test_response_is_required(): void
    {
        Livewire::test(Speaking::class)
            ->set('response', '')
            ->call('submit')
            ->assertHasErrors(['response' => 'required']);
    }
}
