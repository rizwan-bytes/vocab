<?php

namespace Database\Factories;

use App\Enums\WordStatus;
use App\Models\Word;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Word>
 */
class WordFactory extends Factory
{
    protected $model = Word::class;

    public function definition(): array
    {
        $word = $this->faker->unique()->word();

        return [
            'word' => $word,
            'normalized_word' => Word::normalize($word),
            'pronunciation' => '/'.$word.'/',
            'part_of_speech' => $this->faker->randomElement(['noun', 'verb', 'adjective']),
            'simple_definition' => $this->faker->sentence(),
            'detailed_definition' => $this->faker->paragraph(),
            'urdu_meaning' => 'اردو معنی',
            'examples' => [$this->faker->sentence(), $this->faker->sentence()],
            'synonyms' => [$this->faker->word(), $this->faker->word()],
            'antonyms' => [$this->faker->word()],
            'common_collocations' => [$this->faker->words(2, true)],
            'ielts_relevance' => $this->faker->sentence(),
            'difficulty_level' => $this->faker->randomElement(['A2', 'B1', 'B2', 'C1', 'C2']),
            'context' => null,
            'source' => null,
            'status' => WordStatus::New,
            'next_review_at' => now()->addDay(),
            'last_reviewed_at' => null,
        ];
    }

    public function due(): static
    {
        return $this->state(fn () => ['next_review_at' => now()->subMinute()]);
    }

    public function status(WordStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
