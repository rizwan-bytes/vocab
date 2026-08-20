<?php

namespace Tests\Feature\Services;

use App\Enums\ReviewRating;
use App\Enums\WordStatus;
use App\Models\Word;
use App\Services\SpacedRepetitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpacedRepetitionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SpacedRepetitionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SpacedRepetitionService;
    }

    public function test_initial_schedule_is_one_day_away(): void
    {
        $schedule = $this->service->initialSchedule();

        $this->assertEqualsWithDelta(
            now()->addDay()->timestamp,
            $schedule->timestamp,
            5,
        );
    }

    public function test_good_rating_on_a_words_first_ever_review_moves_to_the_second_rung(): void
    {
        // A brand-new word's next_review_at is already "1 day" (set at creation by
        // initialSchedule()) — that's rung index 0 before any review has happened.
        // The word's first actual review therefore advances FROM index 0, not from
        // "nothing yet" — so a Good rating here should land on 3 days (index 1).
        $word = Word::factory()->create([
            'next_review_at' => now()->addDay(),
            'last_reviewed_at' => null,
        ]);

        $result = $this->service->schedule($word, ReviewRating::Good);

        $this->assertEqualsWithDelta(now()->addDays(3)->timestamp, $result['next_review_at']->timestamp, 5);
        $this->assertSame(WordStatus::Learning, $result['status']);
    }

    public function test_good_rating_on_a_word_with_no_schedule_yet_lands_on_the_first_rung(): void
    {
        $word = Word::factory()->create(['next_review_at' => null, 'last_reviewed_at' => null]);

        $result = $this->service->schedule($word, ReviewRating::Good);

        $this->assertEqualsWithDelta(now()->addDays(1)->timestamp, $result['next_review_at']->timestamp, 5);
    }

    public function test_again_rating_always_schedules_a_short_same_day_delay(): void
    {
        $word = Word::factory()->create([
            'next_review_at' => now()->addDays(30),
            'last_reviewed_at' => now()->subDays(14),
        ]);

        $result = $this->service->schedule($word, ReviewRating::Again);

        $this->assertTrue($result['next_review_at']->lessThan(now()->addHour()));
        $this->assertSame(WordStatus::Learning, $result['status']);
    }

    public function test_easy_advances_further_than_good_from_the_same_starting_point(): void
    {
        $goodWord = Word::factory()->create(['next_review_at' => now()->addDays(3), 'last_reviewed_at' => now()]);
        $easyWord = Word::factory()->create(['next_review_at' => now()->addDays(3), 'last_reviewed_at' => now()]);

        $goodResult = $this->service->schedule($goodWord, ReviewRating::Good);
        $easyResult = $this->service->schedule($easyWord, ReviewRating::Easy);

        $this->assertTrue($easyResult['next_review_at']->greaterThan($goodResult['next_review_at']));
    }

    public function test_hard_gives_a_shorter_interval_than_good_without_resetting_progress(): void
    {
        $hardWord = Word::factory()->create(['next_review_at' => now()->addDays(7), 'last_reviewed_at' => now()]);
        $goodWord = Word::factory()->create(['next_review_at' => now()->addDays(7), 'last_reviewed_at' => now()]);

        $hardResult = $this->service->schedule($hardWord, ReviewRating::Hard);
        $goodResult = $this->service->schedule($goodWord, ReviewRating::Good);

        $this->assertTrue($hardResult['next_review_at']->lessThan($goodResult['next_review_at']));
        // Hard should still be well past "same day" — it's not a reset like Again.
        $this->assertTrue($hardResult['next_review_at']->greaterThan(now()->addHours(12)));
    }

    public function test_reaching_the_final_rung_marks_the_word_mastered(): void
    {
        // Simulate a word that's already at the 30-day rung.
        $word = Word::factory()->create(['next_review_at' => now()->addDays(30), 'last_reviewed_at' => now()]);

        $result = $this->service->schedule($word, ReviewRating::Good);

        $this->assertSame(WordStatus::Mastered, $result['status']);
        $this->assertEqualsWithDelta(now()->addDays(60)->timestamp, $result['next_review_at']->timestamp, 5);
    }
}
