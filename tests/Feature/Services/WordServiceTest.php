<?php

namespace Tests\Feature\Services;

use App\Enums\WordStatus;
use App\Models\DailyProgress;
use App\Services\WordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WordServiceTest extends TestCase
{
    use RefreshDatabase;

    private WordService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WordService::class);
    }

    private function geminiData(string $word = 'meticulous'): array
    {
        return [
            'word' => $word,
            'pronunciation' => '/test/',
            'part_of_speech' => 'adjective',
            'simple_definition' => 'A simple definition.',
            'detailed_definition' => 'A detailed definition.',
            'urdu_meaning' => 'اردو معنی',
            'examples' => ['Example one.'],
            'synonyms' => ['careful'],
            'antonyms' => ['careless'],
            'common_collocations' => ['test phrase'],
            'ielts_relevance' => 'Relevant for IELTS.',
            'difficulty_level' => 'B2',
        ];
    }

    public function test_it_creates_a_word_with_the_correct_initial_state(): void
    {
        $word = $this->service->createFromGeminiData($this->geminiData(), 'some context', 'book');

        $this->assertDatabaseHas('words', [
            'word' => 'meticulous',
            'normalized_word' => 'meticulous',
            'status' => WordStatus::New->value,
            'context' => 'some context',
            'source' => 'book',
        ]);

        $this->assertNotNull($word->next_review_at);
        $this->assertTrue($word->next_review_at->isFuture());
    }

    public function test_json_array_fields_round_trip_correctly(): void
    {
        $word = $this->service->createFromGeminiData($this->geminiData(), null, null);

        $word->refresh();

        $this->assertSame(['Example one.'], $word->examples);
        $this->assertSame(['careful'], $word->synonyms);
    }

    public function test_finding_an_existing_word_is_case_insensitive(): void
    {
        $this->service->createFromGeminiData($this->geminiData('Meticulous'), null, null);

        $found = $this->service->findExisting('MeTiCulous');

        $this->assertNotNull($found);
        $this->assertSame('Meticulous', $found->word);
    }

    public function test_finding_an_existing_word_trims_whitespace(): void
    {
        $this->service->createFromGeminiData($this->geminiData('meticulous'), null, null);

        $found = $this->service->findExisting('  meticulous  ');

        $this->assertNotNull($found);
    }

    public function test_a_word_that_does_not_exist_returns_null(): void
    {
        $this->assertNull($this->service->findExisting('nonexistent'));
    }

    public function test_creating_a_word_increments_todays_new_word_count(): void
    {
        $this->service->createFromGeminiData($this->geminiData(), null, null);

        $this->assertSame(1, DailyProgress::query()->where('date', today())->value('new_words'));

        $this->service->createFromGeminiData($this->geminiData('ubiquitous'), null, null);

        $this->assertSame(2, DailyProgress::query()->where('date', today())->value('new_words'));
    }
}
