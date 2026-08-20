<?php

namespace App\Livewire\Progress;

use App\Enums\WordStatus;
use App\Models\DailyProgress;
use App\Models\Word;
use App\Services\DailyProgressService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Progress'])]
class Index extends Component
{
    public function render(DailyProgressService $dailyProgress): View
    {
        $today = DailyProgress::query()->where('date', today())->first();

        $totals = DailyProgress::query()
            ->selectRaw('SUM(correct_answers) as correct, SUM(wrong_answers) as wrong, SUM(study_minutes) as minutes')
            ->first();

        $totalAnswered = ($totals->correct ?? 0) + ($totals->wrong ?? 0);
        $accuracy = $totalAnswered > 0 ? round(($totals->correct / $totalAnswered) * 100, 1) : null;

        $last30Days = DailyProgress::query()
            ->whereBetween('date', [today()->subDays(29), today()])
            ->get()
            ->keyBy(fn (DailyProgress $row) => $row->date->toDateString());

        $chart = collect(range(29, 0))->map(function (int $daysAgo) use ($last30Days) {
            $date = today()->subDays($daysAgo);
            $row = $last30Days->get($date->toDateString());

            return [
                'date' => $date,
                'new_words' => $row->new_words ?? 0,
                'reviewed_words' => $row->reviewed_words ?? 0,
            ];
        });

        return view('livewire.progress.index', [
            'totalWords' => Word::query()->count(),
            'newWordsToday' => $today->new_words ?? 0,
            'reviewsToday' => $today->reviewed_words ?? 0,
            'masteredWords' => Word::query()->status(WordStatus::Mastered)->count(),
            'learningWords' => Word::query()->status(WordStatus::Learning)->count(),
            'currentStreak' => $dailyProgress->currentStreak(),
            'longestStreak' => $dailyProgress->longestStreak(),
            'accuracy' => $accuracy,
            'studyMinutes' => (int) ($totals->minutes ?? 0),
            'chart' => $chart,
        ]);
    }
}
