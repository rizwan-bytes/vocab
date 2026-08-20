<?php

namespace Tests\Feature\Services;

use App\Enums\ReviewRating;
use App\Enums\WordStatus;
use App\Models\Word;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReviewService::class);
    }

    public function test_due_words_are_retrieved_and_not_due_words_are_excluded(): void
    {
        $due = Word::factory()->due()->create();
        Word::factory()->create(['next_review_at' => now()->addDays(5)]); // not due
        Word::factory()->create(['next_review_at' => null]); // never scheduled, not due

        $dueWords = Word::query()->due()->get();

        $this->assertCount(1, $dueWords);
        $this->assertTrue($dueWords->first()->is($due));
    }

    public function test_submitting_a_review_updates_the_words_own_schedule(): void
    {
        $word = Word::factory()->due()->create(['next_review_at' => now()->subMinute()]);

        $this->service->submit($word, ReviewRating::Good);
        $word->refresh();

        $this->assertTrue($word->next_review_at->isFuture());
        $this->assertNotNull($word->last_reviewed_at);
    }

    public function test_submitting_a_review_creates_a_word_review_record(): void
    {
        $word = Word::factory()->due()->create();

        $this->service->submit($word, ReviewRating::Good);

        $this->assertDatabaseHas('word_reviews', [
            'word_id' => $word->id,
            'rating' => ReviewRating::Good->value,
        ]);
    }

    public function test_a_reviewed_word_no_longer_shows_up_as_due(): void
    {
        $word = Word::factory()->due()->create();

        $this->service->submit($word, ReviewRating::Good);

        $this->assertFalse(Word::query()->due()->whereKey($word->id)->exists());
    }

    public function test_correct_and_wrong_counts_accumulate_across_reviews(): void
    {
        $word = Word::factory()->due()->create();

        $this->service->submit($word, ReviewRating::Good); // correct
        $word->update(['next_review_at' => now()->subMinute()]);
        $this->service->submit($word, ReviewRating::Again); // wrong
        $word->update(['next_review_at' => now()->subMinute()]);
        $review = $this->service->submit($word, ReviewRating::Good); // correct

        $this->assertSame(2, $review->correct_count);
        $this->assertSame(1, $review->wrong_count);
    }

    public function test_repeated_good_reviews_eventually_reach_mastered(): void
    {
        $word = Word::factory()->due()->create();

        // Ladder: 1 -> 3 -> 7 -> 14 -> 30 -> 60 days. Good advances one rung each time.
        // Time-travel forward past each scheduled next_review_at (rather than
        // force-overwriting it) so the ladder-position math — which infers where a
        // word is by the gap between its last review and its current schedule —
        // stays internally consistent, matching how this actually happens in
        // production as real days pass.
        foreach ([1, 3, 7, 14, 30, 60] as $days) {
            $this->travel($days + 1)->days();
            $this->service->submit($word->fresh(), ReviewRating::Good);
        }

        $this->assertSame(WordStatus::Mastered, $word->fresh()->status);
    }

    public function test_an_again_rating_drops_status_back_to_learning(): void
    {
        $word = Word::factory()->status(WordStatus::Mastered)->due()->create();

        $this->service->submit($word, ReviewRating::Again);
        $word->refresh();

        $this->assertSame(WordStatus::Learning, $word->status);
    }

    public function test_submitting_a_review_updates_todays_daily_progress(): void
    {
        $word = Word::factory()->due()->create();

        $this->service->submit($word, ReviewRating::Good);

        $this->assertDatabaseHas('daily_progress', [
            'reviewed_words' => 1,
            'correct_answers' => 1,
            'wrong_answers' => 0,
        ]);
    }
}
