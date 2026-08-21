<?php

namespace Database\Factories;

use App\Enums\TutorSessionType;
use App\Models\TutorSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TutorSession>
 */
class TutorSessionFactory extends Factory
{
    protected $model = TutorSession::class;

    public function definition(): array
    {
        return [
            'type' => TutorSessionType::Essay,
            'task_type' => 'task2',
            'prompt_text' => $this->faker->sentence(),
            'submission' => $this->faker->paragraph(),
            'feedback' => [
                'band_overall' => 6.5,
                'task_achievement' => ['score' => 6.5, 'comment' => 'Comment.'],
                'coherence_cohesion' => ['score' => 6.5, 'comment' => 'Comment.'],
                'lexical_resource' => ['score' => 6.5, 'comment' => 'Comment.'],
                'grammar' => ['score' => 6.5, 'comment' => 'Comment.'],
                'summary' => 'Summary.',
                'strengths' => ['Strength one.'],
                'improvements' => ['Improvement one.'],
                'corrections' => [],
            ],
            'band_overall' => 6.5,
            'band_breakdown' => [
                'task_achievement' => ['score' => 6.5, 'comment' => 'Comment.'],
                'coherence_cohesion' => ['score' => 6.5, 'comment' => 'Comment.'],
                'lexical_resource' => ['score' => 6.5, 'comment' => 'Comment.'],
                'grammar' => ['score' => 6.5, 'comment' => 'Comment.'],
            ],
        ];
    }
}
