<?php

namespace App\Prompts;

class EssayFeedbackPrompt
{
    public static function build(string $taskType, ?string $topic, string $essay): string
    {
        $taskLabel = $taskType === 'task1'
            ? 'IELTS Academic Writing Task 1 (describing a chart/graph/diagram/process, 150+ words)'
            : 'IELTS Writing Task 2 (opinion/discussion essay, 250+ words)';

        $topicLine = filled($topic)
            ? "The essay question/topic was: \"{$topic}\""
            : 'No specific question was given — assess the essay on its own terms.';

        return <<<PROMPT
            You are an experienced, encouraging but honest IELTS Writing examiner.
            Assess this {$taskLabel} submission using the official IELTS band descriptors
            (Task Achievement/Response, Coherence and Cohesion, Lexical Resource,
            Grammatical Range and Accuracy).

            {$topicLine}

            ESSAY:
            """
            {$essay}
            """

            Guidelines:
            - Score each of the 4 criteria 0-9 in whole or half bands (e.g. 6.0, 6.5, 7.0), and give band_overall as their average rounded to the nearest half band.
            - Be realistic, not inflated — a band 9 should be genuinely exceptional. Most real submissions from learners land 5.5-7.5.
            - Comments must be specific to THIS essay (quote or reference actual phrases), never generic filler like "good job, keep practicing."
            - strengths and improvements: 2-4 concrete, actionable points each.
            - corrections: pick 3-6 of the most impactful grammar/vocabulary issues actually present in the essay. For each: the original phrase, a corrected version, and a one-sentence explanation of why. Do not invent errors that aren't there — if the essay is genuinely strong, return fewer corrections.
            - Never fabricate praise or criticism not grounded in the actual text.
            - Return ONLY the JSON object described by the response schema. No markdown, no extra commentary.
            PROMPT;
    }

    public static function responseSchema(): array
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
                'task_achievement' => $criterion,
                'coherence_cohesion' => $criterion,
                'lexical_resource' => $criterion,
                'grammar' => $criterion,
                'summary' => ['type' => 'STRING'],
                'strengths' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                'improvements' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                'corrections' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'original' => ['type' => 'STRING'],
                            'corrected' => ['type' => 'STRING'],
                            'explanation' => ['type' => 'STRING'],
                        ],
                        'required' => ['original', 'corrected', 'explanation'],
                    ],
                ],
            ],
            'required' => [
                'band_overall', 'task_achievement', 'coherence_cohesion',
                'lexical_resource', 'grammar', 'summary', 'strengths', 'improvements', 'corrections',
            ],
        ];
    }
}
