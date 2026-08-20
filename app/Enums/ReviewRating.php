<?php

namespace App\Enums;

enum ReviewRating: string
{
    case Again = 'again';
    case Hard = 'hard';
    case Good = 'good';
    case Easy = 'easy';

    public function label(): string
    {
        return match ($this) {
            self::Again => 'Again',
            self::Hard => 'Hard',
            self::Good => 'Good',
            self::Easy => 'Easy',
        };
    }

    /**
     * Whether this rating counts as a correct recall for progress stats.
     * Only "Again" means the word wasn't recalled at all — "Hard" still means the
     * user got it, just with effort, so it counts as correct (matches how
     * Anki-style SRS treats Hard: interval still grows, just more slowly).
     */
    public function isCorrect(): bool
    {
        return $this !== self::Again;
    }
}
