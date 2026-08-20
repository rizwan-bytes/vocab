<?php

namespace App\Services;

use App\Models\DailyProgress;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Owns the "one row per day" upsert logic for the daily_progress table.
 * Both WordService's Add-Word flow and the Review flow call into this rather
 * than writing to daily_progress directly, so the aggregation rules live in
 * exactly one place.
 */
class DailyProgressService
{
    public function recordNewWord(): void
    {
        $this->todayRow()->increment('new_words');
    }

    public function recordReview(bool $wasCorrect, bool $justMastered): void
    {
        $row = $this->todayRow();

        $row->increment('reviewed_words');
        $row->increment($wasCorrect ? 'correct_answers' : 'wrong_answers');

        if ($justMastered) {
            $row->increment('mastered_words');
        }
    }

    public function addStudyMinutes(int $minutes): void
    {
        if ($minutes <= 0) {
            return;
        }

        $this->todayRow()->increment('study_minutes', $minutes);
    }

    private function todayRow(): DailyProgress
    {
        return DB::transaction(
            fn () => DailyProgress::query()->firstOrCreate(['date' => today()])
        );
    }

    /**
     * Consecutive days of activity, counting backward. If today has no
     * activity logged yet, that alone doesn't break the streak (the day
     * isn't over) — counting simply starts from yesterday instead.
     */
    public function currentStreak(): int
    {
        $date = today();

        if (! $this->hasActivity($date)) {
            $date = $date->copy()->subDay();
        }

        $streak = 0;

        while ($this->hasActivity($date)) {
            $streak++;
            $date = $date->copy()->subDay();
        }

        return $streak;
    }

    public function longestStreak(): int
    {
        $activeDates = DailyProgress::query()
            ->where(fn ($q) => $q->where('new_words', '>', 0)->orWhere('reviewed_words', '>', 0))
            ->orderBy('date')
            ->pluck('date');

        $longest = 0;
        $current = 0;
        $previous = null;

        foreach ($activeDates as $date) {
            // Carbon's diffInDays returns a float, so compare with intval rather
            // than a strict === 1, which would silently never match.
            $current = ($previous && (int) $previous->diffInDays($date) === 1) ? $current + 1 : 1;
            $longest = max($longest, $current);
            $previous = $date;
        }

        return $longest;
    }

    private function hasActivity(CarbonInterface $date): bool
    {
        return DailyProgress::query()
            ->where('date', $date)
            ->where(fn ($q) => $q->where('new_words', '>', 0)->orWhere('reviewed_words', '>', 0))
            ->exists();
    }
}
