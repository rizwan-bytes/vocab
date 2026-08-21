<div class="mx-auto max-w-2xl space-y-6">
    <a href="{{ route('tutor.index') }}" wire:navigate
        class="inline-flex items-center gap-1.5 text-sm font-medium text-[var(--color-foreground-muted)] hover:text-[var(--color-foreground)]">
        <x-icon name="chevron-right" class="size-3.5 rotate-180" />
        Tutor
    </a>

    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-[var(--color-foreground)]">Speaking Practice</h1>
        <p class="mt-1 text-sm text-[var(--color-foreground-muted)]">Type-based practice — get feedback on fluency, vocabulary, and grammar. Pronunciation isn't assessed since this isn't audio.</p>
    </div>

    <div class="flex gap-2">
        @foreach (['part1' => 'Part 1', 'part2' => 'Part 2', 'part3' => 'Part 3'] as $value => $label)
            <button type="button" wire:click="changePart('{{ $value }}')"
                class="flex-1 rounded-lg border-2 px-3 py-2 text-sm font-medium transition {{ $part === $value ? 'border-[var(--color-primary)] bg-[var(--color-surface-muted)] text-[var(--color-primary)]' : 'border-[var(--color-border)] text-[var(--color-foreground-muted)]' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($errorMessage)
        <div class="flex items-start gap-2.5 rounded-lg bg-red-50 p-3.5 text-sm text-[var(--color-danger)]">
            <x-icon name="exclamation-triangle" class="mt-0.5 size-4 shrink-0" />
            {{ $errorMessage }}
        </div>
    @endif

    @if (! $question)
        <div class="rounded-2xl border border-dashed border-[var(--color-border)] p-10 text-center">
            <p class="text-sm text-[var(--color-foreground-muted)]">Ready to practice {{ ucfirst(str_replace('part', 'Part ', $part)) }}?</p>
            <button wire:click="getQuestion" type="button"
                wire:loading.attr="disabled" wire:target="getQuestion"
                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-[var(--color-on-primary)] hover:bg-[var(--color-primary-light)] disabled:opacity-60">
                <span wire:loading.remove wire:target="getQuestion">Get a Question</span>
                <span wire:loading wire:target="getQuestion">Generating…</span>
            </button>
        </div>
    @else
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">Question</p>
            <p class="mt-2 whitespace-pre-line text-lg leading-relaxed text-[var(--color-foreground)]">{{ $question }}</p>
        </div>

        <form wire:submit="submit" class="space-y-4">
            <div>
                <label for="response" class="block text-sm font-medium text-[var(--color-foreground)]">Your answer</label>
                <textarea wire:model="response" id="response" rows="8"
                    placeholder="Type your answer as if you were speaking it..."
                    class="mt-1.5 block w-full rounded-lg border-[var(--color-border)] text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]"></textarea>
                @error('response') <p class="mt-1.5 text-sm text-[var(--color-danger)]">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    wire:loading.attr="disabled" wire:target="submit"
                    class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-[var(--color-primary)] px-4 py-3 text-sm font-semibold text-[var(--color-on-primary)] hover:bg-[var(--color-primary-light)] disabled:opacity-60">
                    <span wire:loading.remove wire:target="submit">Get Feedback</span>
                    <span wire:loading wire:target="submit">Analyzing…</span>
                </button>
                <button wire:click="getQuestion" type="button"
                    class="rounded-lg border border-[var(--color-border)] px-4 py-3 text-sm font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-surface-muted)]">
                    New question
                </button>
            </div>
        </form>
    @endif
</div>
