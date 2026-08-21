<?php

namespace App\Prompts;

class SpeakingPracticePrompt
{
    public static function buildQuestion(string $part): string
    {
        $partDescription = match ($part) {
            'part1' => 'IELTS Speaking Part 1 (short questions about familiar everyday topics: home, work, studies, hobbies, etc.)',
            'part2' => 'IELTS Speaking Part 2 (a cue card task — the candidate speaks for 1-2 minutes on a given topic, with 3-4 bullet points to cover)',
            'part3' => 'IELTS Speaking Part 3 (deeper discussion questions connected to the Part 2 topic, more abstract/analytical)',
            default => 'IELTS Speaking',
        };

        return <<<PROMPT
            You are an IELTS Speaking examiner. Generate one realistic {$partDescription} question.

            For Part 2, format the cue card as: a "Describe..." prompt line, followed by 3-4 "You should say:" bullet points, followed by a closing line asking the candidate to explain how they felt about it.

            For Part 1 and Part 3, return a single clear question.

            Return ONLY the JSON object described by the response schema — no markdown, no extra commentary.
            PROMPT;
    }

    public static function questionSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'question' => ['type' => 'STRING'],
            ],
            'required' => ['question'],
        ];
    }

    public static function buildGrading(string $part, string $question, string $response): string
    {
        return <<<PROMPT
            You are an experienced, encouraging but honest IELTS Speaking examiner.
            The candidate was given this {$part} question:

            "{$question}"

            They responded (typed, not spoken — this is text-based practice):
            """
            {$response}
            """

            Assess using 3 of the 4 official IELTS Speaking criteria: Fluency and Coherence,
            Lexical Resource, and Grammatical Range and Accuracy. Do NOT assess Pronunciation —
            this is a typed response, pronunciation cannot be judged from text, and you must not
            fabricate a pronunciation score.

            Guidelines:
            - Score each criterion 0-9 in whole or half bands. band_overall is their average, rounded to the nearest half band.
            - "Fluency and Coherence" from text should focus on idea organisation, logical flow, and natural-sounding phrasing (not hesitation, which can't be observed in text) — treat awkward or robotic phrasing as a fluency/coherence issue.
            - Be realistic, not inflated.
            - vocabulary_suggestions: 2-4 specific upgrades — a plainer word/phrase the candidate used, and a more precise or higher-register alternative, with a short reason. Only suggest real, natural alternatives.
            - strengths and improvements: 2-4 concrete points each, grounded in what was actually said.
            - Return ONLY the JSON object described by the response schema.
            PROMPT;
    }

    public static function gradingSchema(): array
    {
        $criterion = [
            'type' => 'OBJECT',
            'properties' => [
                'score' => ['type' => 'NUMBER'],
                'comment' => ['type' => 'STRING'],
            ],
            'required' => ['score', 'comment'],
        ];

        return [
            'type' => 'OBJECT',
            'properties' => [
                'band_overall' => ['type' => 'NUMBER'],
                'fluency_coherence' => $criterion,
                'lexical_resource' => $criterion,
                'grammar' => $criterion,
                'summary' => ['type' => 'STRING'],
                'strengths' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                'improvements' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                'vocabulary_suggestions' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'instead_of' => ['type' => 'STRING'],
                            'use' => ['type' => 'STRING'],
                            'reason' => ['type' => 'STRING'],
                        ],
                        'required' => ['instead_of', 'use', 'reason'],
                    ],
                ],
            ],
            'required' => [
                'band_overall', 'fluency_coherence', 'lexical_resource', 'grammar',
                'summary', 'strengths', 'improvements', 'vocabulary_suggestions',
            ],
        ];
    }
}
