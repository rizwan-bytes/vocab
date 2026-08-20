<?php

namespace App\Livewire\Review;

use App\Enums\ReviewRating;
use App\Models\Word;
use App\Services\ReviewService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Review'])]
class Index extends Component
{
    public bool $revealed = false;

    /** Reviews completed so far in this session (for the little progress counter). */
    public int $completedCount = 0;

    public function reveal(): void
    {
        $this->revealed = true;
    }

    public function rate(string $rating, ReviewService $reviewService): void
    {
        $word = $this->currentWord();

        if (! $word) {
            return;
        }

        $reviewService->submit($word, ReviewRating::from($rating));

        $this->completedCount++;
        $this->revealed = false;
    }

    private function currentWord(): ?Word
    {
        return Word::query()->due()->orderBy('next_review_at')->first();
    }

    public function render(): View
    {
        return view('livewire.review.index', [
            'word' => $this->currentWord(),
            'remainingCount' => Word::query()->due()->count(),
        ]);
    }
}
