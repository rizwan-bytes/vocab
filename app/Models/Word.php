<?php

namespace App\Models;

use App\Enums\WordStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'word',
        'normalized_word',
        'pronunciation',
        'part_of_speech',
        'simple_definition',
        'detailed_definition',
        'urdu_meaning',
        'examples',
        'synonyms',
        'antonyms',
        'common_collocations',
        'ielts_relevance',
        'difficulty_level',
        'context',
        'source',
        'status',
        'next_review_at',
        'last_reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'examples' => 'array',
            'synonyms' => 'array',
            'antonyms' => 'array',
            'common_collocations' => 'array',
            'status' => WordStatus::class,
            'next_review_at' => 'datetime',
            'last_reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(WordReview::class)->orderBy('created_at');
    }

    public static function normalize(string $word): string
    {
        return mb_strtolower(trim($word));
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->whereNotNull('next_review_at')
            ->where('next_review_at', '<=', now());
    }

    public function scopeStatus(Builder $query, WordStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('word', 'like', "%{$term}%")
                ->orWhere('simple_definition', 'like', "%{$term}%");
        });
    }
}
