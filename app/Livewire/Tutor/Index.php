<?php

namespace App\Livewire\Tutor;

use App\Enums\TutorSessionType;
use App\Models\TutorSession;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'IELTS Tutor'])]
class Index extends Component
{
    public function render(): View
    {
        return view('livewire.tutor.index', [
            'sessionTypes' => TutorSessionType::cases(),
            'recentSessions' => TutorSession::query()->latest()->limit(10)->get(),
        ]);
    }
}
