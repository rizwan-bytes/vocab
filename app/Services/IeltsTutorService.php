<?php

namespace App\Services;

use App\Exceptions\GeminiException;
use App\Models\Word;
use App\Prompts\EssayFeedbackPrompt;
use App\Prompts\SpeakingPracticePrompt;
use App\Prompts\VocabularyExercisePrompt;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * All Gemini interaction for the IELTS Tutor module lives here — Livewire
 * components never call Gemini directly (same isolation rule as
 * GeminiVocabularyService). Structured-output only, no freeform chat: every
 * response is constrained by a Gemini responseSchema and re-validated with
 * Laravel's Validator before the caller ever sees it.
 */
class IeltsTutorService
{
    public function __construct(
        private readonly GeminiClient $client,
    ) {}

    /**
     * @throws GeminiException
     */
    public function generateEssayFeedback(string $taskType, ?string $topic, string $essay): array
    {
        $data = $this->client->generate(
            EssayFeedbackPrompt::build($taskType, $topic, $essay),
            EssayFeedbackPrompt::responseSchema(),
        );

        $criterion = ['required', 'array'];
        $criterionScore = ['required', 'numeric', 'between:0,9'];

        return $this->validate($data, [
            'band_overall' => ['required', 'numeric', 'between:0,9'],
            'task_achievement' => $criterion,
            'task_achievement.score' => $criterionScore,
            'task_achievement.comment' => ['required', 'string'],
            'coherence_cohesion' => $criterion,
            'coherence_cohesion.score' => $criterionScore,
            'coherence_cohesion.comment' => ['required', 'string'],
            'lexical_resource' => $criterion,
            'lexical_resource.score' => $criterionScore,
            'lexical_resource.comment' => ['required', 'string'],
            'grammar' => $criterion,
            'grammar.score' => $criterionScore,
            'grammar.comment' => ['required', 'string'],
            'summary' => ['required', 'string'],
            // "present" not "required" — Laravel's required rule fails on an empty
            // array, but a genuinely strong essay may reasonably have few or zero
            // improvements to suggest.
            'strengths' => ['present', 'array'],
            'improvements' => ['present', 'array'],
            'corrections' => ['nullable', 'array'],
            'corrections.*.original' => ['required', 'string'],
            'corrections.*.corrected' => ['required', 'string'],
            'corrections.*.explanation' => ['required', 'string'],
        ]);
    }

    /**
     * @throws GeminiException
     */
    public function generateSpeakingQuestion(string $part): array
    {
        $data = $this->client->generate(
            SpeakingPracticePrompt::buildQuestion($part),
            SpeakingPracticePrompt::questionSchema(),
        );

        return $this->validate($data, [
            'question' => ['required', 'string'],
        ]);
    }

    /**
     * @throws GeminiException
     */
    public function gradeSpeakingResponse(string $part, string $question, string $response): array
    {
        $data = $this->client->generate(
            SpeakingPracticePrompt::buildGrading($part, $question, $response),
            SpeakingPracticePrompt::gradingSchema(),
        );

        $criterion = ['required', 'array'];
        $criterionScore = ['required', 'numeric', 'between:0,9'];

        return $this->validate($data, [
            'band_overall' => ['required', 'numeric', 'between:0,9'],
            'fluency_coherence' => $criterion,
            'fluency_coherence.score' => $criterionScore,
            'fluency_coherence.comment' => ['required', 'string'],
            'lexical_resource' => $criterion,
            'lexical_resource.score' => $criterionScore,
            'lexical_resource.comment' => ['required', 'string'],
            'grammar' => $criterion,
            'grammar.score' => $criterionScore,
            'grammar.comment' => ['required', 'string'],
            'summary' => ['required', 'string'],
            'strengths' => ['present', 'array'],
            'improvements' => ['present', 'array'],
            'vocabulary_suggestions' => ['nullable', 'array'],
            'vocabulary_suggestions.*.instead_of' => ['required', 'string'],
            'vocabulary_suggestions.*.use' => ['required', 'string'],
            'vocabulary_suggestions.*.reason' => ['required', 'string'],
        ]);
    }

    /**
     * @param  Collection<int, Word>  $words
     *
     * @throws GeminiException
     */
    public function generateVocabularyExercise(Collection $words): array
    {
        $data = $this->client->generate(
            VocabularyExercisePrompt::buildExercise($words),
            VocabularyExercisePrompt::exerciseSchema(),
        );

        return $this->validate($data, [
            'instructions' => ['required', 'string'],
        ]);
    }

    /**
     * @param  Collection<int, Word>  $words
     *
     * @throws GeminiException
     */
    public function gradeVocabularyExercise(Collection $words, string $instructions, string $submission): array
    {
        $data = $this->client->generate(
            VocabularyExercisePrompt::buildGrading($words, $instructions, $submission),
            VocabularyExercisePrompt::gradingSchema(),
        );

        return $this->validate($data, [
            'summary' => ['required', 'string'],
            'word_usage' => ['required', 'array'],
            'word_usage.*.word' => ['required', 'string'],
            'word_usage.*.used' => ['required', 'boolean'],
            'word_usage.*.used_correctly' => ['required', 'boolean'],
            'word_usage.*.feedback' => ['required', 'string'],
            'overall_feedback' => ['required', 'string'],
        ]);
    }

    /**
     * @throws GeminiException
     */
    private function validate(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            Log::error('IELTS Tutor: Gemini response failed validation.', [
                'errors' => $validator->errors()->toArray(),
                'data' => $data,
            ]);
            throw GeminiException::invalidData();
        }

        return $data;
    }
}
