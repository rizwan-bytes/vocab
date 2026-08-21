<?php

namespace App\Prompts;

use App\Models\Word;
use Illuminate\Support\Collection;

class VocabularyExercisePrompt
{
    /**
     * @param  Collection<int, Word>  $words
     */
    public static function buildExercise(Collection $words): string
    {
        $wordList = $words->map(fn ($w) => "- {$w->word} ({$w->part_of_speech}): {$w->simple_definition}")->implode("\n");

        return <<<PROMPT
            You are an IELTS vocabulary coach. The learner is currently studying these words:

            {$wordList}

            Create ONE short writing exercise (2-4 sentences, or a short paragraph) that asks the
            learner to write about a simple everyday or IELTS-relevant topic (e.g. their routine,
            an opinion, a place, a past experience) while naturally using ALL of the words above
            at least once each. Keep the topic simple and concrete — this is active-recall
            vocabulary practice, not a creative writing test.

            Return ONLY the JSON object described by the response schema.
            PROMPT;
    }

    public static function exerciseSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'instructions' => ['type' => 'STRING'],
            ],
            'required' => ['instructions'],
        ];
    }

    /**
     * @param  Collection<int, Word>  $words
     */
    public static function buildGrading(Collection $words, string $instructions, string $submission): string
    {
        $wordList = $words->pluck('word')->implode(', ');

        return <<<PROMPT
            You are an IELTS vocabulary coach reviewing a learner's practice writing.

            The exercise instructions were:
            "{$instructions}"

            The target words to use were: {$wordList}

            The learner wrote:
            """
            {$submission}
            """

            For EACH target word, judge whether it was used, and whether it was used correctly
            (right meaning, right part of speech, natural in context). Be encouraging but honest —
            if a word is missing or misused, say so clearly and show a corrected example.

            Return ONLY the JSON object described by the response schema.
            PROMPT;
    }

    public static function gradingSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'summary' => ['type' => 'STRING'],
                'word_usage' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'word' => ['type' => 'STRING'],
                            'used' => ['type' => 'BOOLEAN'],
                            'used_correctly' => ['type' => 'BOOLEAN'],
                            'feedback' => ['type' => 'STRING'],
                        ],
                        'required' => ['word', 'used', 'used_correctly', 'feedback'],
                    ],
                ],
                'overall_feedback' => ['type' => 'STRING'],
            ],
            'required' => ['summary', 'word_usage', 'overall_feedback'],
        ];
    }
}
