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

#[Layout('components.layouts.app', ['title' => 'Speaking Practice'])]
class Speaking extends Component
{
    public string $part = 'part1';

    public ?string $question = null;

    #[Validate('required|string|min:5|max:4000')]
    public string $response = '';

    public ?string $errorMessage = null;

    public function getQuestion(IeltsTutorService $tutor): void
    {
        $this->errorMessage = null;
        $this->question = null;
        $this->response = '';

        try {
            $result = $tutor->generateSpeakingQuestion($this->part);
            $this->question = $result['question'];
        } catch (GeminiException $e) {
            Log::warning('Speaking practice: question generation failed.', ['message' => $e->getMessage()]);
            $this->errorMessage = $e->getMessage();
        }
    }

    public function changePart(string $part): void
    {
        $this->part = $part;
        $this->question = null;
        $this->response = '';
        $this->errorMessage = null;
    }

    public function submit(IeltsTutorService $tutor): void
    {
        $this->validate();
        $this->errorMessage = null;

        try {
            $feedback = $tutor->gradeSpeakingResponse($this->part, $this->question, $this->response);
        } catch (GeminiException $e) {
            Log::warning('Speaking practice: grading failed.', ['message' => $e->getMessage()]);
            $this->errorMessage = $e->getMessage();

            return;
        }

        $session = TutorSession::create([
            'type' => TutorSessionType::Speaking,
            'task_type' => $this->part,
            'prompt_text' => $this->question,
            'submission' => $this->response,
            'feedback' => $feedback,
            'band_overall' => $feedback['band_overall'],
            'band_breakdown' => [
                'fluency_coherence' => $feedback['fluency_coherence'],
                'lexical_resource' => $feedback['lexical_resource'],
                'grammar' => $feedback['grammar'],
            ],
        ]);

        $this->redirectRoute('tutor.show', $session, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.tutor.speaking');
    }
}
