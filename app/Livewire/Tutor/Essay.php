<?php

namespace App\Livewire\Tutor;

use App\Enums\TutorSessionType;
use App\Exceptions\GeminiException;
use App\Models\TutorSession;
use App\Services\IeltsTutorService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Essay Feedback'])]
class Essay extends Component
{
    #[Validate('required|in:task1,task2')]
    public string $taskType = 'task2';

    #[Validate('nullable|string|max:2000')]
    public ?string $topic = null;

    #[Validate('required|string|min:50|max:8000')]
    public string $essay = '';

    public ?string $errorMessage = null;

    public function submit(IeltsTutorService $tutor): void
    {
        $this->validate();
        $this->errorMessage = null;

        try {
            $feedback = $tutor->generateEssayFeedback($this->taskType, $this->topic ?: null, $this->essay);
        } catch (GeminiException $e) {
            Log::warning('Essay feedback: Gemini generation failed.', ['message' => $e->getMessage()]);
            $this->errorMessage = $e->getMessage();

            return;
        }

        $session = TutorSession::create([
            'type' => TutorSessionType::Essay,
            'task_type' => $this->taskType,
            'prompt_text' => $this->topic ?: null,
            'submission' => $this->essay,
            'feedback' => $feedback,
            'band_overall' => $feedback['band_overall'],
            'band_breakdown' => [
                'task_achievement' => $feedback['task_achievement'],
                'coherence_cohesion' => $feedback['coherence_cohesion'],
                'lexical_resource' => $feedback['lexical_resource'],
                'grammar' => $feedback['grammar'],
            ],
        ]);

        $this->redirectRoute('tutor.show', $session, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.tutor.essay');
    }
}
