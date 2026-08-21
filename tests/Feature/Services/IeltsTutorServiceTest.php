<?php

namespace Tests\Feature\Services;

use App\Exceptions\GeminiException;
use App\Models\Word;
use App\Services\IeltsTutorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IeltsTutorServiceTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGemini(array $data): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake([
            '*generateContent*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode($data)]]]]],
            ]),
        ]);
    }

    private function criterion(float $score = 6.5): array
    {
        return ['score' => $score, 'comment' => 'Some specific comment.'];
    }

    // ── Essay feedback ──────────────────────────────────────

    public function test_it_parses_valid_essay_feedback(): void
    {
        $this->fakeGemini([
            'band_overall' => 6.5,
            'task_achievement' => $this->criterion(),
            'coherence_cohesion' => $this->criterion(),
            'lexical_resource' => $this->criterion(),
            'grammar' => $this->criterion(),
            'summary' => 'Solid attempt overall.',
            'strengths' => ['Clear structure.'],
            'improvements' => ['Vary sentence openers.'],
            'corrections' => [
                ['original' => 'a lot of people', 'corrected' => 'many people', 'explanation' => 'More formal register.'],
            ],
        ]);

        $result = app(IeltsTutorService::class)->generateEssayFeedback('task2', 'Some topic', 'My essay text.');

        $this->assertSame(6.5, $result['band_overall']);
        $this->assertCount(1, $result['corrections']);
    }

    public function test_it_throws_when_essay_feedback_is_missing_a_criterion(): void
    {
        $this->fakeGemini([
            'band_overall' => 6.5,
            'task_achievement' => $this->criterion(),
            // missing coherence_cohesion, lexical_resource, grammar
            'summary' => 'x',
            'strengths' => [],
            'improvements' => [],
        ]);

        $this->expectException(GeminiException::class);

        app(IeltsTutorService::class)->generateEssayFeedback('task2', null, 'text');
    }

    public function test_it_throws_when_band_score_is_out_of_range(): void
    {
        $this->fakeGemini([
            'band_overall' => 12,
            'task_achievement' => $this->criterion(),
            'coherence_cohesion' => $this->criterion(),
            'lexical_resource' => $this->criterion(),
            'grammar' => $this->criterion(),
            'summary' => 'x',
            'strengths' => [],
            'improvements' => [],
            'corrections' => [],
        ]);

        $this->expectException(GeminiException::class);

        app(IeltsTutorService::class)->generateEssayFeedback('task2', null, 'text');
    }

    // ── Speaking ─────────────────────────────────────────────

    public function test_it_generates_a_speaking_question(): void
    {
        $this->fakeGemini(['question' => 'Describe a place you like to visit.']);

        $result = app(IeltsTutorService::class)->generateSpeakingQuestion('part2');

        $this->assertSame('Describe a place you like to visit.', $result['question']);
    }

    public function test_it_grades_a_speaking_response(): void
    {
        $this->fakeGemini([
            'band_overall' => 6.0,
            'fluency_coherence' => $this->criterion(6.0),
            'lexical_resource' => $this->criterion(6.0),
            'grammar' => $this->criterion(6.0),
            'summary' => 'Reasonable response.',
            'strengths' => ['Good structure.'],
            'improvements' => ['Use more linking words.'],
            'vocabulary_suggestions' => [
                ['instead_of' => 'good', 'use' => 'beneficial', 'reason' => 'More precise.'],
            ],
        ]);

        $result = app(IeltsTutorService::class)->gradeSpeakingResponse('part1', 'Do you like your job?', 'Yes I like it.');

        $this->assertEquals(6.0, $result['band_overall']);
        $this->assertCount(1, $result['vocabulary_suggestions']);
    }

    public function test_speaking_grading_does_not_require_a_pronunciation_score(): void
    {
        // Deliberately not including a pronunciation key — the schema must not require one,
        // since pronunciation can't be assessed from typed text.
        $this->fakeGemini([
            'band_overall' => 6.0,
            'fluency_coherence' => $this->criterion(),
            'lexical_resource' => $this->criterion(),
            'grammar' => $this->criterion(),
            'summary' => 'x',
            'strengths' => [],
            'improvements' => [],
            'vocabulary_suggestions' => [],
        ]);

        $result = app(IeltsTutorService::class)->gradeSpeakingResponse('part1', 'q', 'a');

        $this->assertArrayNotHasKey('pronunciation', $result);
    }

    // ── Vocabulary practice ─────────────────────────────────

    public function test_it_generates_a_vocabulary_exercise(): void
    {
        $words = Word::factory()->count(2)->create();
        $this->fakeGemini(['instructions' => 'Write a short paragraph using both words.']);

        $result = app(IeltsTutorService::class)->generateVocabularyExercise($words);

        $this->assertSame('Write a short paragraph using both words.', $result['instructions']);
    }

    public function test_it_grades_a_vocabulary_exercise(): void
    {
        $words = Word::factory()->count(1)->create(['word' => 'meticulous']);
        $this->fakeGemini([
            'summary' => 'Good use of the word.',
            'word_usage' => [
                ['word' => 'meticulous', 'used' => true, 'used_correctly' => true, 'feedback' => 'Used correctly.'],
            ],
            'overall_feedback' => 'Well done.',
        ]);

        $result = app(IeltsTutorService::class)->gradeVocabularyExercise($words, 'instructions', 'my submission');

        $this->assertTrue($result['word_usage'][0]['used_correctly']);
    }
}
