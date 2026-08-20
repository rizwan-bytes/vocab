<div class="space-y-6">
    <h1 class="text-2xl font-semibold tracking-tight text-[var(--color-foreground)]">Progress</h1>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
            <p class="text-2xl font-semibold tabular-nums text-[var(--color-foreground)]">{{ $totalWords }}</p>
            <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">Total words</p>
        </div>
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
            <p class="text-2xl font-semibold tabular-nums text-[var(--color-foreground)]">{{ $newWordsToday }}</p>
            <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">New today</p>
        </div>
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
            <p class="text-2xl font-semibold tabular-nums text-[var(--color-foreground)]">{{ $reviewsToday }}</p>
            <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">Reviews today</p>
        </div>
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
            <p class="text-2xl font-semibold tabular-nums text-[var(--color-status-mastered)]">{{ $masteredWords }}</p>
            <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">Mastered</p>
        </div>
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
            <p class="text-2xl font-semibold tabular-nums text-[var(--color-status-learning)]">{{ $learningWords }}</p>
            <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">Learning</p>
        </div>
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
            <p class="flex items-center gap-1.5 text-2xl font-semibold tabular-nums text-[var(--color-accent)]">
                <x-icon name="flame" class="size-5" />
                {{ $currentStreak }}
            </p>
            <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">Current streak</p>
        </div>
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
            <p class="text-2xl font-semibold tabular-nums text-[var(--color-foreground)]">{{ $longestStreak }}</p>
            <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">Longest streak</p>
        </div>
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
            <p class="text-2xl font-semibold tabular-nums text-[var(--color-foreground)]">{{ $accuracy !== null ? $accuracy.'%' : '—' }}</p>
            <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">Review accuracy</p>
        </div>
    </div>

    {{-- 30-day activity --}}
    <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
        <h2 class="text-sm font-semibold text-[var(--color-foreground)]">Last 30 days</h2>
        <div class="mt-4 flex items-end gap-[3px]" style="height: 96px">
            @php $max = max(1, $chart->flatMap(fn ($d) => [$d['new_words'], $d['reviewed_words']])->max()); @endphp
            @foreach ($chart as $day)
                <div class="group relative flex h-full flex-1 items-end justify-center gap-[1px]">
                    <div class="w-full rounded-t bg-[var(--color-primary)]" style="height: {{ $day['new_words'] > 0 ? max(6, ($day['new_words'] / $max) * 100) : 0 }}%"></div>
                    <div class="w-full rounded-t bg-[var(--color-accent)]" style="height: {{ $day['reviewed_words'] > 0 ? max(6, ($day['reviewed_words'] / $max) * 100) : 0 }}%"></div>
                    <div class="pointer-events-none absolute -top-9 left-1/2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-[var(--color-foreground)] px-2 py-1 text-[10px] font-medium text-white group-hover:block">
                        {{ $day['date']->format('M j') }}: {{ $day['new_words'] }} new, {{ $day['reviewed_words'] }} reviewed
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4 flex items-center gap-4 text-xs text-[var(--color-foreground-muted)]">
            <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-[var(--color-primary)]"></span> New words</span>
            <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-[var(--color-accent)]"></span> Reviews</span>
        </div>
    </div>
</div>
