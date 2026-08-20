<?php

namespace App\Services;

use App\Enums\ReviewRating;
use App\Enums\WordStatus;
use App\Models\Word;
use Carbon\CarbonImmutable;

/**
 * Deterministic, MVP-simple spaced repetition — a fixed interval ladder, not
 * an adaptive academic algorithm (SM-2 etc.), per the project's own
 * "do not overengineer" constraint. All scheduling math lives here, never in
 * a Livewire component or Blade view.
 *
 * Ladder (days from the moment of a "Good" review): 1 → 3 → 7 → 14 → 30 → 60.
 * The word's position on the ladder isn't stored as a separate column — it's
 * re-derived each time from the interval that produced the word's current
 * `next_review_at`, which keeps the schema simple (see Word migration notes).
 */
class SpacedRepetitionService
{
    private const LADDER = [1, 3, 7, 14, 30, 60];

    private const AGAIN_DELAY_MINUTES = 10;

    /**
     * @return array{next_review_at: CarbonImmutable, status: WordStatus}
     */
    public function schedule(Word $word, ReviewRating $rating): array
    {
        $previousIndex = $this->currentLadderIndex($word);

        if ($rating === ReviewRating::Again) {
            return [
                'next_review_at' => now()->toImmutable()->addMinutes(self::AGAIN_DELAY_MINUTES),
                'status' => WordStatus::Learning,
            ];
        }

        $nextIndex = match ($rating) {
            ReviewRating::Hard => $previousIndex,
            ReviewRating::Good => min($previousIndex + 1, $this->maxIndex()),
            ReviewRating::Easy => min($previousIndex + 2, $this->maxIndex()),
        };

        $intervalDays = $rating === ReviewRating::Hard
            ? max(1, intdiv(self::LADDER[$nextIndex], 2))
            : self::LADDER[$nextIndex];

        return [
            'next_review_at' => now()->toImmutable()->addDays($intervalDays),
            'status' => $this->statusForIndex($nextIndex),
        ];
    }

    /** The ladder index (0-based) that produced the word's current schedule. */
    private function currentLadderIndex(Word $word): int
    {
        if (! $word->next_review_at) {
            return -1;
        }

        $anchor = $word->last_reviewed_at ?? $word->created_at;
        $days = (int) round($anchor->diffInHours($word->next_review_at) / 24);

        $closest = -1;
        foreach (self::LADDER as $index => $rungDays) {
            if ($days >= $rungDays) {
                $closest = $index;
            }
        }

        return $closest;
    }

    private function statusForIndex(int $index): WordStatus
    {
        return match (true) {
            $index >= $this->maxIndex() => WordStatus::Mastered,
            $index >= 2 => WordStatus::Reviewing,
            default => WordStatus::Learning,
        };
    }

    private function maxIndex(): int
    {
        return count(self::LADDER) - 1;
    }

    /** The schedule for a brand-new word that has never been reviewed. */
    public function initialSchedule(): CarbonImmutable
    {
        return now()->toImmutable()->addDays(self::LADDER[0]);
    }
}
