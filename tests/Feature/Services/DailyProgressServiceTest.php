<?php

namespace Tests\Feature\Services;

use App\Models\DailyProgress;
use App\Services\DailyProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    private DailyProgressService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DailyProgressService::class);
    }

    public function test_recording_a_new_word_creates_or_increments_todays_row(): void
    {
        $this->service->recordNewWord();
        $this->service->recordNewWord();

        $this->assertSame(2, DailyProgress::query()->where('date', today())->value('new_words'));
        $this->assertSame(1, DailyProgress::query()->count());
    }

    public function test_recording_a_correct_review_increments_correct_answers(): void
    {
        $this->service->recordReview(wasCorrect: true, justMastered: false);
        $this->service->recordReview(wasCorrect: false, justMastered: false);

        $row = DailyProgress::query()->where('date', today())->first();

        $this->assertSame(2, $row->reviewed_words);
        $this->assertSame(1, $row->correct_answers);
        $this->assertSame(1, $row->wrong_answers);
    }

    public function test_recording_a_just_mastered_review_increments_mastered_count(): void
    {
        $this->service->recordReview(wasCorrect: true, justMastered: true);

        $this->assertSame(1, DailyProgress::query()->where('date', today())->value('mastered_words'));
    }

    public function test_current_streak_is_zero_with_no_activity(): void
    {
        $this->assertSame(0, $this->service->currentStreak());
    }

    public function test_current_streak_counts_consecutive_active_days_ending_today(): void
    {
        DailyProgress::factory()->create(['date' => today(), 'new_words' => 1]);
        DailyProgress::factory()->create(['date' => today()->subDay(), 'new_words' => 1]);
        DailyProgress::factory()->create(['date' => today()->subDays(2), 'reviewed_words' => 3]);
        DailyProgress::factory()->create(['date' => today()->subDays(4), 'new_words' => 1]); // gap on day -3

        $this->assertSame(3, $this->service->currentStreak());
    }

    public function test_current_streak_does_not_break_if_today_has_no_activity_yet(): void
    {
        DailyProgress::factory()->create(['date' => today()->subDay(), 'new_words' => 1]);
        DailyProgress::factory()->create(['date' => today()->subDays(2), 'new_words' => 1]);
        // No row for today yet — day isn't "over", so streak should still count.

        $this->assertSame(2, $this->service->currentStreak());
    }

    public function test_longest_streak_finds_the_best_historical_run(): void
    {
        // A 3-day streak in the past, then a gap, then today (1-day streak).
        DailyProgress::factory()->create(['date' => today()->subDays(10), 'new_words' => 1]);
        DailyProgress::factory()->create(['date' => today()->subDays(9), 'new_words' => 1]);
        DailyProgress::factory()->create(['date' => today()->subDays(8), 'new_words' => 1]);
        DailyProgress::factory()->create(['date' => today(), 'new_words' => 1]);

        $this->assertSame(3, $this->service->longestStreak());
    }
}
