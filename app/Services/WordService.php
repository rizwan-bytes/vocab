<?php

namespace App\Services;

use App\Enums\WordStatus;
use App\Models\Word;
use Illuminate\Support\Facades\DB;

/**
 * Word creation + duplicate-detection logic. Kept out of the Livewire
 * component so "what counts as a duplicate" and "what a brand-new word's
 * initial state looks like" are testable in isolation.
 */
class WordService
{
    public function __construct(
        private readonly SpacedRepetitionService $spacedRepetition,
        private readonly DailyProgressService $dailyProgress,
    ) {}

    public function findExisting(string $word): ?Word
    {
        return Word::query()
            ->where('normalized_word', Word::normalize($word))
            ->first();
    }

    /**
     * @param  array<string, mixed>  $geminiData  Already-validated data from GeminiVocabularyService.
     */
    public function createFromGeminiData(array $geminiData, ?string $context, ?string $source): Word
    {
        return DB::transaction(function () use ($geminiData, $context, $source) {
            $word = $this->store($geminiData, $context, $source);
            $this->dailyProgress->recordNewWord();

            return $word;
        });
    }

    private function store(array $geminiData, ?string $context, ?string $source): Word
    {
        return Word::create([
            'word' => $geminiData['word'],
            'normalized_word' => Word::normalize($geminiData['word']),
            'pronunciation' => $geminiData['pronunciation'] ?? null,
            'part_of_speech' => $geminiData['part_of_speech'] ?? null,
            'simple_definition' => $geminiData['simple_definition'],
            'detailed_definition' => $geminiData['detailed_definition'] ?? null,
            'urdu_meaning' => $geminiData['urdu_meaning'],
            'examples' => $geminiData['examples'] ?? [],
            'synonyms' => $geminiData['synonyms'] ?? [],
            'antonyms' => $geminiData['antonyms'] ?? [],
            'common_collocations' => $geminiData['common_collocations'] ?? [],
            'ielts_relevance' => $geminiData['ielts_relevance'] ?? null,
            'difficulty_level' => $geminiData['difficulty_level'] ?? null,
            'context' => $context,
            'source' => $source,
            'status' => WordStatus::New,
            'next_review_at' => $this->spacedRepetition->initialSchedule(),
        ]);
    }
}
