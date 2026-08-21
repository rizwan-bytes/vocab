<?php

namespace App\Livewire\Tutor;

use App\Models\TutorSession;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Tutor Feedback'])]
class Show extends Component
{
    public TutorSession $tutorSession;

    public function mount(TutorSession $tutorSession): void
    {
        $this->tutorSession = $tutorSession;
    }

    public function delete(): void
    {
        $this->tutorSession->delete();
        $this->redirectRoute('tutor.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.tutor.show', [
            'words' => $this->tutorSession->words(),
        ]);
    }
}
