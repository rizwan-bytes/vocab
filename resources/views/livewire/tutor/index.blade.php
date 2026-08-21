<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-[var(--color-foreground)]">IELTS Tutor</h1>
        <p class="mt-1 text-sm text-[var(--color-foreground-muted)]">Practice writing, speaking, and using your vocabulary — with structured, honest feedback.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <a href="{{ route('tutor.essay') }}" wire:navigate
            class="group rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 transition hover:border-[var(--color-primary)] hover:shadow-sm">
            <span class="flex size-10 items-center justify-center rounded-lg bg-[var(--color-surface-muted)] text-[var(--color-primary)] group-hover:bg-[var(--color-primary)] group-hover:text-[var(--color-on-primary)]">
                <x-icon name="pencil" class="size-5" />
            </span>
            <h2 class="mt-3 font-semibold text-[var(--color-foreground)]">Essay Feedback</h2>
            <p class="mt-1 text-sm text-[var(--color-foreground-muted)]">Submit a Writing Task 1 or 2 essay and get a band-score breakdown with specific corrections.</p>
        </a>

        <a href="{{ route('tutor.speaking') }}" wire:navigate
            class="group rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 transition hover:border-[var(--color-primary)] hover:shadow-sm">
            <span class="flex size-10 items-center justify-center rounded-lg bg-[var(--color-surface-muted)] text-[var(--color-primary)] group-hover:bg-[var(--color-primary)] group-hover:text-[var(--color-on-primary)]">
                <x-icon name="sparkles" class="size-5" />
            </span>
            <h2 class="mt-3 font-semibold text-[var(--color-foreground)]">Speaking Practice</h2>
            <p class="mt-1 text-sm text-[var(--color-foreground-muted)]">Get a Part 1/2/3 question, type your answer, and get fluency, vocabulary, and grammar feedback.</p>
        </a>

        <a href="{{ route('tutor.vocabulary-practice') }}" wire:navigate
            class="group rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 transition hover:border-[var(--color-primary)] hover:shadow-sm">
            <span class="flex size-10 items-center justify-center rounded-lg bg-[var(--color-surface-muted)] text-[var(--color-primary)] group-hover:bg-[var(--color-primary)] group-hover:text-[var(--color-on-primary)]">
                <x-icon name="book-open" class="size-5" />
            </span>
            <h2 class="mt-3 font-semibold text-[var(--color-foreground)]">Vocabulary Practice</h2>
            <p class="mt-1 text-sm text-[var(--color-foreground-muted)]">Write using words from your own vocabulary list and see if you used them correctly.</p>
        </a>
    </div>

    <div>
        <h2 class="text-sm font-semibold text-[var(--color-foreground)]">Recent sessions</h2>

        @if ($recentSessions->isEmpty())
            <div class="mt-3 rounded-xl border border-dashed border-[var(--color-border)] p-8 text-center text-sm text-[var(--color-foreground-muted)]">
                No tutor sessions yet — try Essay Feedback, Speaking Practice, or Vocabulary Practice above.
            </div>
        @else
            <div class="mt-3 divide-y divide-[var(--color-border)] overflow-hidden rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)]">
                @foreach ($recentSessions as $session)
                    <a href="{{ route('tutor.show', $session) }}" wire:navigate
                        class="flex items-center justify-between gap-4 p-4 transition hover:bg-[var(--color-surface-muted)]">
                        <div class="flex items-center gap-3">
                            <span class="flex size-9 items-center justify-center rounded-lg bg-[var(--color-surface-muted)] text-[var(--color-primary)]">
                                <x-icon :name="$session->type->icon()" class="size-4" />
                            </span>
                            <div>
                                <p class="text-sm font-medium text-[var(--color-foreground)]">{{ $session->type->label() }}</p>
                                <p class="text-xs text-[var(--color-foreground-muted)]">{{ $session->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        @if ($session->band_overall !== null)
                            <span class="rounded-full bg-[var(--color-surface-muted)] px-2.5 py-1 text-xs font-semibold text-[var(--color-primary)]">
                                Band {{ $session->band_overall }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
