<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyProgress extends Model
{
    use HasFactory;

    protected $table = 'daily_progress';

    protected $fillable = [
        'user_id',
        'date',
        'new_words',
        'reviewed_words',
        'mastered_words',
        'correct_answers',
        'wrong_answers',
        'study_minutes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accuracy(): float
    {
        $total = $this->correct_answers + $this->wrong_answers;

        return $total > 0
            ? round(($this->correct_answers / $total) * 100, 1)
            : 0.0;
    }
}
