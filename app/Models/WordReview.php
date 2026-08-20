<?php

namespace App\Models;

use App\Enums\ReviewRating;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'word_id',
        'review_date',
        'rating',
        'next_review_at',
        'correct_count',
        'wrong_count',
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'rating' => ReviewRating::class,
            'next_review_at' => 'datetime',
        ];
    }

    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }
}
