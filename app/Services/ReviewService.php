<?php

namespace App\Services;

use App\Enums\ReviewRating;
use App\Enums\WordStatus;
use App\Models\Word;
use App\Models\WordReview;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates a single review submission: scheduling (via
 * SpacedRepetitionService), the WordReview history row, the Word's own
 * denormalized next_review_at/status, and today's DailyProgress row — all in
 * one transaction so a mid-way failure can never leave these out of sync.
 */
class ReviewService
{
    public function __construct(
        private readonly SpacedRepetitionService $spacedRepetition,
        private readonly DailyProgressService $dailyProgress,
    ) {}

    public function submit(Word $word, ReviewRating $rating): WordReview
    {
        return DB::transaction(function () use ($word, $rating) {
            $schedule = $this->spacedRepetition->schedule($word, $rating);
            $wasCorrect = $rating->isCorrect();

            $lastReview = $word->reviews()->latest('id')->first();
            $correctCount = ($lastReview?->correct_count ?? 0) + ($wasCorrect ? 1 : 0);
            $wrongCount = ($lastReview?->wrong_count ?? 0) + ($wasCorrect ? 0 : 1);

            $review = $word->reviews()->create([
                'review_date' => today(),
                'rating' => $rating,
                'next_review_at' => $schedule['next_review_at'],
                'correct_count' => $correctCount,
                'wrong_count' => $wrongCount,
            ]);

            $justMastered = $schedule['status'] === WordStatus::Mastered
                && $word->status !== WordStatus::Mastered;

            $word->update([
                'status' => $schedule['status'],
                'next_review_at' => $schedule['next_review_at'],
                'last_reviewed_at' => now(),
            ]);

            $this->dailyProgress->recordReview($wasCorrect, $justMastered);

            return $review;
        });
    }
}
