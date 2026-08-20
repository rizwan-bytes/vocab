<?php

namespace App\Prompts;

/**
 * Centralizes the Gemini prompt + expected response schema for vocabulary
 * generation. Keeping this in one place (rather than inline in the service)
 * makes the prompt easy to review/version without touching API-call logic.
 */
class VocabularyPrompt
{
    public const VERSION = 1;

    public static function build(string $word, ?string $context = null): string
    {
        $contextLine = filled($context)
            ? "The word was found in this sentence/context: \"{$context}\"\nUse this context to pick the most relevant sense of the word if it has multiple meanings."
            : 'No context sentence was provided — use the most common general meaning.';

        return <<<PROMPT
            You are a vocabulary tutor helping an intermediate-to-advanced English
            learner who is preparing for the IELTS exam. The learner's native
            language is Urdu.

            Explain the English word: "{$word}"

            {$contextLine}

            Guidelines:
            - Write definitions in clear, natural English a learner can understand — avoid unnecessarily academic phrasing.
            - The Urdu meaning must be accurate and natural, not a literal/awkward translation.
            - Give 2-3 realistic example sentences that show natural usage, not textbook-stiff ones.
            - List real, commonly-used synonyms and antonyms (omit antonyms entirely if the word genuinely has none — return an empty list, don't invent weak ones).
            - Collocations should be phrases a learner would actually encounter or use, not made up combinations.
            - The IELTS relevance note should be specific and practical: where/how this word is actually useful in IELTS Writing or Speaking, not a generic "this is a useful word" filler.
            - difficulty_level must be a single CEFR level: A1, A2, B1, B2, C1, or C2.
            - part_of_speech must be a single lowercase word: noun, verb, adjective, adverb, preposition, conjunction, pronoun, or interjection.
            - Return ONLY the JSON object described by the response schema. No extra commentary, no markdown formatting.
            PROMPT;
    }

    /**
     * Gemini's structured-output schema (Vertex/Generative Language API
     * "responseSchema" format) — constrains the model to return exactly this
     * shape, which is far more reliable than parsing free-form JSON text.
     */
    public static function responseSchema(): array
    {
        $stringArray = ['type' => 'ARRAY', 'items' => ['type' => 'STRING']];

        return [
            'type' => 'OBJECT',
            'properties' => [
                'word' => ['type' => 'STRING'],
                'pronunciation' => ['type' => 'STRING'],
                'part_of_speech' => ['type' => 'STRING'],
                'simple_definition' => ['type' => 'STRING'],
                'urdu_meaning' => ['type' => 'STRING'],
                'detailed_definition' => ['type' => 'STRING'],
                'examples' => $stringArray,
                'synonyms' => $stringArray,
                'antonyms' => $stringArray,
                'common_collocations' => $stringArray,
                'ielts_relevance' => ['type' => 'STRING'],
                'difficulty_level' => ['type' => 'STRING'],
            ],
            'required' => [
                'word',
                'pronunciation',
                'part_of_speech',
                'simple_definition',
                'urdu_meaning',
                'detailed_definition',
                'examples',
                'synonyms',
                'antonyms',
                'common_collocations',
                'ielts_relevance',
                'difficulty_level',
            ],
        ];
    }
}
