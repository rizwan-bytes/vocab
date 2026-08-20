<?php

namespace App\Livewire\Dashboard;

use App\Enums\WordStatus;
use App\Models\DailyProgress;
use App\Models\Word;
use App\Services\DailyProgressService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public function render(DailyProgressService $dailyProgress): View
    {
        $today = DailyProgress::query()->where('date', today())->first();

        $activity = DailyProgress::query()
            ->whereBetween('date', [today()->subDays(6), today()])
            ->get()
            ->keyBy(fn (DailyProgress $row) => $row->date->toDateString());

        $last7Days = collect(range(6, 0))->map(function (int $daysAgo) use ($activity) {
            $date = today()->subDays($daysAgo);
            $row = $activity->get($date->toDateString());

            return [
                'label' => $date->format('D'),
                'new_words' => $row->new_words ?? 0,
                'reviewed_words' => $row->reviewed_words ?? 0,
            ];
        });

        return view('livewire.dashboard.index', [
            'newWordsToday' => $today->new_words ?? 0,
            'reviewsDueToday' => Word::query()->due()->count(),
            'totalWords' => Word::query()->count(),
            'masteredWords' => Word::query()->status(WordStatus::Mastered)->count(),
            'currentStreak' => $dailyProgress->currentStreak(),
            'last7Days' => $last7Days,
        ]);
    }
}
