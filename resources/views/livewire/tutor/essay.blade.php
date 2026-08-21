<div class="mx-auto max-w-2xl space-y-6">
    <a href="{{ route('tutor.index') }}" wire:navigate
        class="inline-flex items-center gap-1.5 text-sm font-medium text-[var(--color-foreground-muted)] hover:text-[var(--color-foreground)]">
        <x-icon name="chevron-right" class="size-3.5 rotate-180" />
        Tutor
    </a>

    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-[var(--color-foreground)]">Essay Feedback</h1>
        <p class="mt-1 text-sm text-[var(--color-foreground-muted)]">Paste your essay and get a band-score breakdown with specific corrections.</p>
    </div>

    <form wire:submit="submit" class="space-y-4 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
        <div>
            <span class="block text-sm font-medium text-[var(--color-foreground)]">Task</span>
            <div class="mt-1.5 flex gap-2">
                <label class="flex flex-1 cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 px-3 py-2 text-sm font-medium transition {{ $taskType === 'task1' ? 'border-[var(--color-primary)] bg-[var(--color-surface-muted)] text-[var(--color-primary)]' : 'border-[var(--color-border)] text-[var(--color-foreground-muted)]' }}">
                    <input wire:model.live="taskType" type="radio" value="task1" class="sr-only">
                    Task 1
                </label>
                <label class="flex flex-1 cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 px-3 py-2 text-sm font-medium transition {{ $taskType === 'task2' ? 'border-[var(--color-primary)] bg-[var(--color-surface-muted)] text-[var(--color-primary)]' : 'border-[var(--color-border)] text-[var(--color-foreground-muted)]' }}">
                    <input wire:model.live="taskType" type="radio" value="task2" class="sr-only">
                    Task 2
                </label>
            </div>
        </div>

        <div>
            <label for="topic" class="block text-sm font-medium text-[var(--color-foreground)]">
                Question / topic <span class="font-normal text-[var(--color-foreground-muted)]">(optional, but helps accuracy)</span>
            </label>
            <textarea wire:model="topic" id="topic" rows="2"
                placeholder="e.g. Some people think that... To what extent do you agree or disagree?"
                class="mt-1.5 block w-full rounded-lg border-[var(--color-border)] text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]"></textarea>
        </div>

        <div>
            <label for="essay" class="block text-sm font-medium text-[var(--color-foreground)]">Your essay</label>
            <textarea wire:model="essay" id="essay" rows="12"
                placeholder="Paste or write your essay here..."
                class="mt-1.5 block w-full rounded-lg border-[var(--color-border)] text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]"></textarea>
            @error('essay') <p class="mt-1.5 text-sm text-[var(--color-danger)]">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-[var(--color-foreground-muted)]">{{ str_word_count($essay) }} words</p>
        </div>

        @if ($errorMessage)
            <div class="flex items-start gap-2.5 rounded-lg bg-red-50 p-3.5 text-sm text-[var(--color-danger)]">
                <x-icon name="exclamation-triangle" class="mt-0.5 size-4 shrink-0" />
                {{ $errorMessage }}
            </div>
        @endif

        <button type="submit"
            wire:loading.attr="disabled" wire:target="submit"
            class="flex w-full items-center justify-center gap-2 rounded-lg bg-[var(--color-primary)] px-4 py-3 text-sm font-semibold text-[var(--color-on-primary)] transition hover:bg-[var(--color-primary-light)] disabled:cursor-not-allowed disabled:opacity-60">
            <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">
                <x-icon name="sparkles" class="size-4" />
                Get Feedback
            </span>
            <span wire:loading wire:target="submit" class="flex items-center gap-2">
                <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                Analyzing your essay…
            </span>
        </button>
    </form>
</div>
