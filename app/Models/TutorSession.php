<?php

namespace App\Models;

use App\Enums\TutorSessionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'task_type',
        'prompt_text',
        'submission',
        'feedback',
        'band_overall',
        'band_breakdown',
        'word_ids',
    ];

    protected function casts(): array
    {
        return [
            'type' => TutorSessionType::class,
            'feedback' => 'array',
            'band_overall' => 'float',
            'band_breakdown' => 'array',
            'word_ids' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return Collection<int, Word> */
    public function words(): Collection
    {
        if (blank($this->word_ids)) {
            return new Collection;
        }

        return Word::query()->whereIn('id', $this->word_ids)->get();
    }

    public function scopeType(Builder $query, TutorSessionType $type): Builder
    {
        return $query->where('type', $type);
    }
}
