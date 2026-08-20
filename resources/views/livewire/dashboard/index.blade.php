<div class="space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-[var(--color-foreground)]">Today</h1>
                <p class="mt-1 text-sm text-[var(--color-foreground-muted)]">{{ now()->format('l, F j') }}</p>
            </div>

            @if ($currentStreak > 0)
                <div class="flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1.5 text-sm font-semibold text-[var(--color-accent)]">
                    <x-icon name="flame" class="size-4" />
                    {{ $currentStreak }} day{{ $currentStreak === 1 ? '' : 's' }}
                </div>
            @endif
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
                <p class="text-2xl font-semibold tabular-nums text-[var(--color-foreground)]">{{ $newWordsToday }}</p>
                <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">New words</p>
            </div>
            <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
                <p class="text-2xl font-semibold tabular-nums text-[var(--color-foreground)]">{{ $reviewsDueToday }}</p>
                <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">Reviews due</p>
            </div>
            <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
                <p class="text-2xl font-semibold tabular-nums text-[var(--color-foreground)]">{{ $totalWords }}</p>
                <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">Total vocabulary</p>
            </div>
            <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
                <p class="text-2xl font-semibold tabular-nums text-[var(--color-status-mastered)]">{{ $masteredWords }}</p>
                <p class="mt-1 text-xs font-medium text-[var(--color-foreground-muted)]">Mastered</p>
            </div>
        </div>

        {{-- Primary actions --}}
        <div class="grid gap-3 sm:grid-cols-2">
            <a href="{{ route('vocabulary.create') }}" wire:navigate
                class="flex items-center justify-center gap-2 rounded-xl bg-[var(--color-primary)] px-5 py-4 text-base font-semibold text-[var(--color-on-primary)] shadow-sm transition hover:bg-[var(--color-primary-light)] active:scale-[0.98]">
                <x-icon name="plus" class="size-5" />
                Add Word
            </a>

            @if ($reviewsDueToday > 0)
                <a href="{{ route('review.index') }}" wire:navigate
                    class="flex items-center justify-center gap-2 rounded-xl border-2 border-[var(--color-primary)] bg-[var(--color-surface)] px-5 py-4 text-base font-semibold text-[var(--color-primary)] transition hover:bg-[var(--color-surface-muted)] active:scale-[0.98]">
                    <x-icon name="arrow-path" class="size-5" />
                    Start Review ({{ $reviewsDueToday }})
                </a>
            @else
                <div class="flex items-center justify-center gap-2 rounded-xl border-2 border-dashed border-[var(--color-border)] px-5 py-4 text-base font-medium text-[var(--color-foreground-muted)]">
                    <x-icon name="check" class="size-5" />
                    All caught up
                </div>
            @endif
        </div>

        {{-- 7-day activity --}}
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
            <h2 class="text-sm font-semibold text-[var(--color-foreground)]">Last 7 days</h2>
            <div class="mt-4 flex items-end justify-between gap-2" style="height: 96px">
                @php $max = max(1, $last7Days->flatMap(fn ($d) => [$d['new_words'], $d['reviewed_words']])->max()); @endphp
                @foreach ($last7Days as $day)
                    <div class="flex flex-1 flex-col items-center gap-1.5">
                        <div class="flex h-full w-full items-end justify-center gap-1">
                            <div class="w-2.5 rounded-t bg-[var(--color-primary)]" style="height: {{ $day['new_words'] > 0 ? max(8, ($day['new_words'] / $max) * 100) : 0 }}%" title="{{ $day['new_words'] }} new"></div>
                            <div class="w-2.5 rounded-t bg-[var(--color-accent)]" style="height: {{ $day['reviewed_words'] > 0 ? max(8, ($day['reviewed_words'] / $max) * 100) : 0 }}%" title="{{ $day['reviewed_words'] }} reviewed"></div>
                        </div>
                        <span class="text-[10px] font-medium text-[var(--color-foreground-muted)]">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex items-center gap-4 text-xs text-[var(--color-foreground-muted)]">
                <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-[var(--color-primary)]"></span> New words</span>
                <span class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-[var(--color-accent)]"></span> Reviews</span>
            </div>
        </div>
    </div>
