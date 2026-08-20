<?php

namespace App\Services;

use App\Exceptions\GeminiException;
use App\Prompts\VocabularyPrompt;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * All Gemini API interaction lives here — Livewire components must never call
 * Gemini directly. Responsible for sending the prompt, parsing the response,
 * validating its shape, and translating any failure into a GeminiException
 * with a message safe to show the user (no raw API details leak out).
 */
class GeminiVocabularyService
{
    /**
     * @return array<string, mixed> Validated vocabulary data, keyed to match
     *                              the `words` table columns directly.
     *
     * @throws GeminiException
     */
    public function generate(string $word, ?string $context = null): array
    {
        $apiKey = config('gemini.api_key');

        if (blank($apiKey)) {
            Log::error('Gemini API key is not configured.');
            throw GeminiException::apiUnavailable();
        }

        $model = config('gemini.model');
        $url = config('gemini.base_url')."/models/{$model}:generateContent";

        try {
            $response = Http::timeout(config('gemini.timeout'))
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post($url, [
                    'contents' => [
                        ['parts' => [['text' => VocabularyPrompt::build($word, $context)]]],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => VocabularyPrompt::responseSchema(),
                        'temperature' => 0.4,
                    ],
                ]);
        } catch (ConnectionException $e) {
            Log::error('Gemini API connection failed.', ['error' => $e->getMessage()]);
            throw GeminiException::timeout();
        }

        if ($response->status() === 429) {
            Log::warning('Gemini API rate limit hit.');
            throw GeminiException::rateLimited();
        }

        if ($response->failed()) {
            Log::error('Gemini API returned an error.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw GeminiException::apiUnavailable();
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (blank($text)) {
            Log::error('Gemini response had no text content.', ['body' => $response->json()]);
            throw GeminiException::malformedResponse();
        }

        $data = json_decode($text, associative: true);

        if (! is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Gemini response text was not valid JSON.', ['text' => $text]);
            throw GeminiException::malformedResponse();
        }

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
