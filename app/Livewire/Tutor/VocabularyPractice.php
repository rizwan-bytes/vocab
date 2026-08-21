<?php

namespace App\Livewire\Tutor;

use App\Enums\TutorSessionType;
use App\Enums\WordStatus;
use App\Exceptions\GeminiException;
use App\Models\TutorSession;
use App\Models\Word;
use App\Services\IeltsTutorService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Vocabulary Practice'])]
class VocabularyPractice extends Component
{
    /** @var array<int> */
    public array $wordIds = [];

    public ?string $instructions = null;

    #[Validate('required|string|min:10|max:4000')]
    public string $submission = '';

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->wordIds = $this->pickWords()->pluck('id')->all();
    }

    /**
     * Prioritize words still being actively learned (per the app's own goal of
     * turning passive recognition into active recall) — fall back to any
     * words if the user doesn't have enough in those statuses yet.
     */
    private function pickWords()
    {
        $learning = Word::query()
            ->whereIn('status', [WordStatus::Learning->value, WordStatus::Reviewing->value])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        if ($learning->count() >= 2) {
            return $learning;
        }

        return Word::query()->inRandomOrder()->limit(4)->get();
    }

    public function shuffleWords(): void
    {
        $this->wordIds = $this->pickWords()->pluck('id')->all();
        $this->instructions = null;
        $this->submission = '';
        $this->errorMessage = null;
    }

    public function generateExercise(IeltsTutorService $tutor): void
    {
        $this->errorMessage = null;
        $words = $this->words();

        try {
            $result = $tutor->generateVocabularyExercise($words);
            $this->instructions = $result['instructions'];
        } catch (GeminiException $e) {
            Log::warning('Vocabulary practice: exercise generation failed.', ['message' => $e->getMessage()]);
            $this->errorMessage = $e->getMessage();
        }
    }

    public function submit(IeltsTutorService $tutor): void
    {
        $this->validate();
        $this->errorMessage = null;
        $words = $this->words();

        try {
            $feedback = $tutor->gradeVocabularyExercise($words, $this->instructions, $this->submission);
        } catch (GeminiException $e) {
            Log::warning('Vocabulary practice: grading failed.', ['message' => $e->getMessage()]);
            $this->errorMessage = $e->getMessage();

            return;
        }

        $session = TutorSession::create([
            'type' => TutorSessionType::VocabularyPractice,
            'prompt_text' => $this->instructions,
            'submission' => $this->submission,
            'feedback' => $feedback,
            'word_ids' => $this->wordIds,
        ]);

        $this->redirectRoute('tutor.show', $session, navigate: true);
    }

    private function words()
    {
        return Word::query()->whereIn('id', $this->wordIds)->get();
    }

    public function render(): View
    {
        return view('livewire.tutor.vocabulary-practice', [
            'words' => $this->words(),
        ]);
    }
}
