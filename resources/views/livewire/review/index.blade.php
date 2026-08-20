<div class="mx-auto max-w-xl">
    @if (! $word)
        <div class="rounded-2xl border border-dashed border-[var(--color-border)] p-12 text-center">
            <x-icon name="check" class="mx-auto size-10 text-[var(--color-status-mastered)]" />
            <h1 class="mt-4 text-xl font-semibold text-[var(--color-foreground)]">All caught up!</h1>
            <p class="mt-1.5 text-sm text-[var(--color-foreground-muted)]">
                @if ($completedCount > 0)
                    You reviewed {{ $completedCount }} word{{ $completedCount === 1 ? '' : 's' }} this session. Nice work.
                @else
                    No words are due for review right now.
                @endif
            </p>
            <a href="{{ route('dashboard') }}" wire:navigate
                class="mt-5 inline-flex items-center gap-1.5 rounded-lg bg-[var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-[var(--color-on-primary)] hover:bg-[var(--color-primary-light)]">
                Back to dashboard
            </a>
        </div>
    @else
        <div class="mb-4 flex items-center justify-between text-sm text-[var(--color-foreground-muted)]">
            <span>{{ $remainingCount }} word{{ $remainingCount === 1 ? '' : 's' }} due</span>
            @if ($completedCount > 0)
                <span>{{ $completedCount }} reviewed this session</span>
            @endif
        </div>

        <div wire:key="word-{{ $word->id }}" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-8 text-center sm:p-12">
            <p class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">What does this word mean?</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-[var(--color-foreground)]">{{ $word->word }}</h1>
            @if ($word->pronunciation)
                <p class="mt-1.5 font-mono text-sm text-[var(--color-foreground-muted)]">{{ $word->pronunciation }}</p>
            @endif

            @if (! $revealed)
                <button wire:click="reveal" type="button"
                    class="mt-8 inline-flex items-center gap-2 rounded-lg bg-[var(--color-primary)] px-6 py-3 text-sm font-semibold text-[var(--color-on-primary)] hover:bg-[var(--color-primary-light)] active:scale-95">
                    <x-icon name="sparkles" class="size-4" />
                    Reveal Answer
                </button>
            @else
                <div class="mt-6 space-y-4 border-t border-[var(--color-border)] pt-6 text-left">
                    <p class="text-lg leading-relaxed text-[var(--color-foreground)]">{{ $word->simple_definition }}</p>

                    @if ($word->urdu_meaning)
                        <p lang="ur" class="font-urdu text-xl text-[var(--color-foreground-muted)]">{{ $word->urdu_meaning }}</p>
                    @endif

                    @if (filled($word->examples))
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">Example</p>
                            <p class="mt-1 text-sm italic text-[var(--color-foreground)]">{{ $word->examples[0] }}</p>
                        </div>
                    @endif
                </div>

                <div class="mt-8 grid grid-cols-4 gap-2">
                    <button wire:click="rate('again')" type="button"
                        class="flex flex-col items-center gap-1 rounded-lg border-2 border-red-200 bg-red-50 px-2 py-3 text-sm font-semibold text-[var(--color-danger)] transition hover:bg-red-100 active:scale-95">
                        Again
                    </button>
                    <button wire:click="rate('hard')" type="button"
                        class="flex flex-col items-center gap-1 rounded-lg border-2 border-amber-200 bg-amber-50 px-2 py-3 text-sm font-semibold text-[var(--color-warning)] transition hover:bg-amber-100 active:scale-95">
                        Hard
                    </button>
                    <button wire:click="rate('good')" type="button"
                        class="flex flex-col items-center gap-1 rounded-lg border-2 border-[var(--color-primary)] bg-[var(--color-surface-muted)] px-2 py-3 text-sm font-semibold text-[var(--color-primary)] transition hover:bg-teal-100 active:scale-95">
                        Good
                    </button>
                    <button wire:click="rate('easy')" type="button"
                        class="flex flex-col items-center gap-1 rounded-lg border-2 border-emerald-200 bg-emerald-50 px-2 py-3 text-sm font-semibold text-[var(--color-status-mastered)] transition hover:bg-emerald-100 active:scale-95">
                        Easy
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>
