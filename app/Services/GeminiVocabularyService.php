<?php

namespace App\Services;

use App\Exceptions\GeminiException;
use App\Prompts\VocabularyPrompt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * All Gemini vocabulary-card generation lives here — Livewire components
 * must never call Gemini directly. Uses GeminiClient for the actual API
 * transport; this class owns the vocabulary-specific prompt, schema, and
 * validation rules on top of it.
 */
class GeminiVocabularyService
{
    public function __construct(
        private readonly GeminiClient $client,
    ) {}

    /**
     * @return array<string, mixed> Validated vocabulary data, keyed to match
     *                              the `words` table columns directly.
     *
     * @throws GeminiException
     */
    public function generate(string $word, ?string $context = null): array
    {
        $data = $this->client->generate(
            VocabularyPrompt::build($word, $context),
            VocabularyPrompt::responseSchema(),
        );

        return $this->validated($data, $word);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws GeminiException
     */
    private function validated(array $data, string $requestedWord): array
    {
        $validator = Validator::make($data, [
            'word' => ['required', 'string', 'max:100'],
            'pronunciation' => ['nullable', 'string', 'max:100'],
            'part_of_speech' => ['nullable', 'string', 'max:50'],
            'simple_definition' => ['required', 'string', 'max:1000'],
            'urdu_meaning' => ['required', 'string', 'max:1000'],
            'detailed_definition' => ['nullable', 'string', 'max:2000'],
            'examples' => ['nullable', 'array'],
            'examples.*' => ['string', 'max:500'],
            'synonyms' => ['nullable', 'array'],
            'synonyms.*' => ['string', 'max:100'],
            'antonyms' => ['nullable', 'array'],
            'antonyms.*' => ['string', 'max:100'],
            'common_collocations' => ['nullable', 'array'],
            'common_collocations.*' => ['string', 'max:150'],
            'ielts_relevance' => ['nullable', 'string', 'max:1000'],
            'difficulty_level' => ['nullable', 'string', 'in:A1,A2,B1,B2,C1,C2'],
        ]);

        if ($validator->fails()) {
            Log::error('Gemini response failed validation.', [
                'word' => $requestedWord,
                'errors' => $validator->errors()->toArray(),
                'data' => $data,
            ]);
            throw GeminiException::invalidData();
        }

        return $validator->validated();
    }
}
