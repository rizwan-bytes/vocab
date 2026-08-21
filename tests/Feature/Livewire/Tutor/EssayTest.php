<?php

namespace Tests\Feature\Livewire\Tutor;

use App\Livewire\Tutor\Essay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class EssayTest extends TestCase
{
    use RefreshDatabase;

    private function fakeFeedback(): array
    {
        return [
            'band_overall' => 6.5,
            'task_achievement' => ['score' => 6.5, 'comment' => 'x'],
            'coherence_cohesion' => ['score' => 6.5, 'comment' => 'x'],
            'lexical_resource' => ['score' => 6.5, 'comment' => 'x'],
            'grammar' => ['score' => 6.5, 'comment' => 'x'],
            'summary' => 'x',
            'strengths' => ['Good structure.'],
            'improvements' => ['Vary vocabulary.'],
            'corrections' => [],
        ];
    }

    public function test_it_renders_successfully(): void
    {
        Livewire::test(Essay::class)->assertStatus(200);
    }

    public function test_essay_is_required_and_has_a_minimum_length(): void
    {
        Livewire::test(Essay::class)
            ->set('essay', 'too short')
            ->call('submit')
            ->assertHasErrors(['essay' => 'min']);
    }

    public function test_submitting_a_valid_essay_creates_a_session_and_redirects(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode($this->fakeFeedback())]]]]],
            ]),
        ]);

        Livewire::test(Essay::class)
            ->set('taskType', 'task2')
            ->set('essay', str_repeat('This is a reasonably long essay sentence. ', 5))
            ->call('submit')
            ->assertRedirect();

        $this->assertDatabaseHas('tutor_sessions', [
            'type' => 'essay',
            'task_type' => 'task2',
            'band_overall' => 6.5,
        ]);
    }

    public function test_a_gemini_failure_shows_an_error_not_a_crash(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake(['*generateContent*' => Http::response('', 500)]);

        Livewire::test(Essay::class)
            ->set('essay', str_repeat('This is a reasonably long essay sentence. ', 5))
            ->call('submit')
            ->assertSet('errorMessage', fn ($m) => filled($m));

        $this->assertDatabaseCount('tutor_sessions', 0);
    }
}
