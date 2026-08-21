<?php

namespace App\Services;

use App\Exceptions\GeminiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Low-level Gemini API transport — sends a prompt (+ optional structured
 * output schema), parses the response into a PHP array, and translates every
 * failure mode into a GeminiException with a message safe to show the user.
 *
 * This is deliberately dumb: it knows nothing about vocabulary cards, essays,
 * or any other domain shape. Higher-level services (GeminiVocabularyService,
 * IeltsTutorService) own their own prompts, schemas, and validation rules on
 * top of this — keeping "how to talk to Gemini" separate from "what we ask
 * Gemini for."
 */
class GeminiClient
{
    /**
     * @param  array<string, mixed>|null  $schema  Gemini responseSchema, when structured JSON output is needed.
     * @return array<string, mixed> The parsed JSON response body (not yet domain-validated).
     *
     * @throws GeminiException
     */
    public function generate(string $prompt, ?array $schema = null, float $temperature = 0.4): array
    {
        $apiKey = config('gemini.api_key');

        if (blank($apiKey)) {
            Log::error('Gemini API key is not configured.');
            throw GeminiException::apiUnavailable();
        }

        $model = config('gemini.model');
        $url = config('gemini.base_url')."/models/{$model}:generateContent";

        $generationConfig = ['temperature' => $temperature];

        if ($schema) {
            $generationConfig['responseMimeType'] = 'application/json';
            $generationConfig['responseSchema'] = $schema;
        }

        try {
            $response = Http::timeout(config('gemini.timeout'))
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post($url, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => $generationConfig,
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

        return $data;
    }
}
