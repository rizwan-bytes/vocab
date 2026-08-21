<?php

namespace Tests\Feature\Livewire\Tutor;

use App\Enums\TutorSessionType;
use App\Livewire\Tutor\Show;
use App\Models\TutorSession;
use App\Models\Word;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_an_essay_session(): void
    {
        $session = TutorSession::factory()->create();

        Livewire::test(Show::class, ['tutorSession' => $session])
            ->assertStatus(200)
            ->assertSee('Band Breakdown')
            ->assertSee('Task Achievement');
    }

    public function test_it_renders_a_vocabulary_practice_session(): void
    {
        $words = Word::factory()->count(1)->create(['word' => 'meticulous']);

        $session = TutorSession::factory()->create([
            'type' => TutorSessionType::VocabularyPractice,
            'task_type' => null,
            'band_overall' => null,
            'band_breakdown' => null,
            'word_ids' => $words->pluck('id')->all(),
            'feedback' => [
                'summary' => 'Good work.',
                'word_usage' => [
                    ['word' => 'meticulous', 'used' => true, 'used_correctly' => true, 'feedback' => 'Correct.'],
                ],
                'overall_feedback' => 'Keep it up.',
            ],
        ]);

        Livewire::test(Show::class, ['tutorSession' => $session])
            ->assertStatus(200)
            ->assertSee('meticulous')
            ->assertSee('Correct.');
    }

    public function test_deleting_removes_the_session_and_redirects(): void
    {
        $session = TutorSession::factory()->create();

        Livewire::test(Show::class, ['tutorSession' => $session])
            ->call('delete')
            ->assertRedirect(route('tutor.index'));

        $this->assertDatabaseMissing('tutor_sessions', ['id' => $session->id]);
    }
}
