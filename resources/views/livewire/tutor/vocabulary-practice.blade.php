<div class="mx-auto max-w-2xl space-y-6">
    <a href="{{ route('tutor.index') }}" wire:navigate
        class="inline-flex items-center gap-1.5 text-sm font-medium text-[var(--color-foreground-muted)] hover:text-[var(--color-foreground)]">
        <x-icon name="chevron-right" class="size-3.5 rotate-180" />
        Tutor
    </a>

    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-[var(--color-foreground)]">Vocabulary Practice</h1>
        <p class="mt-1 text-sm text-[var(--color-foreground-muted)]">Write using your own saved words — active recall turns them from recognized to remembered.</p>
    </div>

    @if ($words->isEmpty())
        <div class="rounded-2xl border border-dashed border-[var(--color-border)] p-10 text-center">
            <x-icon name="book-open" class="mx-auto size-8 text-[var(--color-foreground-muted)]" />
            <p class="mt-3 text-sm font-medium text-[var(--color-foreground)]">You don't have any saved words yet.</p>
            <a href="{{ route('vocabulary.create') }}" wire:navigate
                class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-[var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-[var(--color-on-primary)] hover:bg-[var(--color-primary-light)]">
                Add your first word
            </a>
        </div>
    @else
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">Words for this exercise</p>
                @unless ($instructions)
                    <button wire:click="shuffleWords" type="button" class="text-xs font-medium text-[var(--color-primary)] hover:underline">
                        Shuffle
                    </button>
                @endunless
            </div>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach ($words as $word)
                    <span class="rounded-full bg-[var(--color-surface-muted)] px-2.5 py-1 text-sm font-medium text-[var(--color-primary)]">{{ $word->word }}</span>
                @endforeach
            </div>
        </div>

        @if ($errorMessage)
            <div class="flex items-start gap-2.5 rounded-lg bg-red-50 p-3.5 text-sm text-[var(--color-danger)]">
                <x-icon name="exclamation-triangle" class="mt-0.5 size-4 shrink-0" />
                {{ $errorMessage }}
            </div>
        @endif

        @if (! $instructions)
            <div class="rounded-2xl border border-dashed border-[var(--color-border)] p-10 text-center">
                <button wire:click="generateExercise" type="button"
                    wire:loading.attr="disabled" wire:target="generateExercise"
                    class="inline-flex items-center gap-2 rounded-lg bg-[var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-[var(--color-on-primary)] hover:bg-[var(--color-primary-light)] disabled:opacity-60">
                    <span wire:loading.remove wire:target="generateExercise" class="flex items-center gap-2">
                        <x-icon name="sparkles" class="size-4" />
                        Generate Exercise
                    </span>
                    <span wire:loading wire:target="generateExercise">Generating…</span>
                </button>
            </div>
        @else
            <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">Exercise</p>
                <p class="mt-2 whitespace-pre-line text-base leading-relaxed text-[var(--color-foreground)]">{{ $instructions }}</p>
            </div>

            <form wire:submit="submit" class="space-y-4">
                <div>
                    <label for="submission" class="block text-sm font-medium text-[var(--color-foreground)]">Your writing</label>
                    <textarea wire:model="submission" id="submission" rows="6"
                        placeholder="Write your response here, using the words above..."
                        class="mt-1.5 block w-full rounded-lg border-[var(--color-border)] text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]"></textarea>
                    @error('submission') <p class="mt-1.5 text-sm text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                    wire:loading.attr="disabled" wire:target="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-[var(--color-primary)] px-4 py-3 text-sm font-semibold text-[var(--color-on-primary)] hover:bg-[var(--color-primary-light)] disabled:opacity-60">
                    <span wire:loading.remove wire:target="submit">Get Feedback</span>
                    <span wire:loading wire:target="submit">Checking your writing…</span>
                </button>
            </form>
        @endif
    @endif
</div>
