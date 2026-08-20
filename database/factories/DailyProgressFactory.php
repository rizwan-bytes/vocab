<?php

namespace Database\Factories;

use App\Models\DailyProgress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyProgress>
 */
class DailyProgressFactory extends Factory
{
    protected $model = DailyProgress::class;

    public function definition(): array
    {
        return [
            'date' => today(),
            'new_words' => 0,
            'reviewed_words' => 0,
            'mastered_words' => 0,
            'correct_answers' => 0,
            'wrong_answers' => 0,
            'study_minutes' => 0,
        ];
    }
}
