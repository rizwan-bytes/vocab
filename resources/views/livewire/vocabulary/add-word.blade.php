<div class="mx-auto max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[var(--color-foreground)]">Add a word</h1>
            <p class="mt-1 text-sm text-[var(--color-foreground-muted)]">Enter a word you didn't know — Gemini will build a full vocabulary card for it.</p>
        </div>

        @if (! $generatedData && ! $existingWord)
            {{-- Step 1: input form --}}
            <form wire:submit="generate" class="space-y-4 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
                <div>
                    <label for="word" class="block text-sm font-medium text-[var(--color-foreground)]">Word</label>
                    <input wire:model="word" id="word" type="text" autofocus autocomplete="off"
                        placeholder="e.g. meticulous"
                        class="mt-1.5 block w-full rounded-lg border-[var(--color-border)] text-lg focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]">
                    @error('word') <p class="mt-1.5 text-sm text-[var(--color-danger)]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="context" class="block text-sm font-medium text-[var(--color-foreground)]">
                        Context <span class="font-normal text-[var(--color-foreground-muted)]">(optional)</span>
                    </label>
                    <textarea wire:model="context" id="context" rows="2"
                        placeholder="The sentence where you found this word..."
                        class="mt-1.5 block w-full rounded-lg border-[var(--color-border)] text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]"></textarea>
                    @error('context') <p class="mt-1.5 text-sm text-[var(--color-danger)]">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-[var(--color-foreground-muted)]">Helps pick the right meaning if the word has more than one.</p>
                </div>

                <div>
                    <label for="source" class="block text-sm font-medium text-[var(--color-foreground)]">
                        Source <span class="font-normal text-[var(--color-foreground-muted)]">(optional)</span>
                    </label>
                    <select wire:model="source" id="source"
                        class="mt-1.5 block w-full rounded-lg border-[var(--color-border)] text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]">
                        <option value="">Not specified</option>
                        @foreach ($sources as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($errorMessage)
                    <div class="flex items-start gap-2.5 rounded-lg bg-red-50 p-3.5 text-sm text-[var(--color-danger)]">
                        <x-icon name="exclamation-triangle" class="mt-0.5 size-4 shrink-0" />
                        <div>
                            <p>{{ $errorMessage }}</p>
                            <button type="button" wire:click="retry" class="mt-1 font-semibold underline">Try again</button>
                        </div>
                    </div>
                @endif

                <button type="submit"
                    wire:loading.attr="disabled" wire:target="generate"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-[var(--color-primary)] px-4 py-3 text-sm font-semibold text-[var(--color-on-primary)] transition hover:bg-[var(--color-primary-light)] disabled:cursor-not-allowed disabled:opacity-60">
                    <span wire:loading.remove wire:target="generate" class="flex items-center gap-2">
                        <x-icon name="sparkles" class="size-4" />
                        Generate
                    </span>
                    <span wire:loading wire:target="generate" class="flex items-center gap-2">
                        <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                        Generating…
                    </span>
                </button>
            </form>
        @endif

        @if ($existingWord)
            {{-- Duplicate found --}}
            <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
                <div class="flex items-start gap-2.5 rounded-lg bg-amber-50 p-3.5 text-sm text-[var(--color-warning)]">
                    <x-icon name="exclamation-triangle" class="mt-0.5 size-4 shrink-0" />
                    <p>"{{ $existingWord->word }}" is already in your vocabulary — showing the saved card instead of generating a new one.</p>
                </div>

                <div class="mt-4">
                    <x-vocabulary-card
                        :word="$existingWord->word"
                        :pronunciation="$existingWord->pronunciation"
                        :part-of-speech="$existingWord->part_of_speech"
                        :simple-definition="$existingWord->simple_definition"
                        :detailed-definition="$existingWord->detailed_definition"
                        :urdu-meaning="$existingWord->urdu_meaning"
                        :examples="$existingWord->examples ?? []"
                        :synonyms="$existingWord->synonyms ?? []"
                        :antonyms="$existingWord->antonyms ?? []"
                        :collocations="$existingWord->common_collocations ?? []"
                        :ielts-relevance="$existingWord->ielts_relevance"
                        :difficulty-level="$existingWord->difficulty_level"
                        :status="$existingWord->status"
                        class="!border-0 !p-0"
                    />
                </div>

                <div class="mt-5 flex gap-3">
                    <a href="{{ route('vocabulary.show', $existingWord) }}" wire:navigate
                        class="flex-1 rounded-lg bg-[var(--color-primary)] px-4 py-2.5 text-center text-sm font-semibold text-[var(--color-on-primary)] hover:bg-[var(--color-primary-light)]">
                        View word
                    </a>
                    <button wire:click="discard" type="button"
                        class="flex-1 rounded-lg border border-[var(--color-border)] px-4 py-2.5 text-sm font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-surface-muted)]">
                        Add a different word
                    </button>
                </div>
            </div>
        @endif

        @if ($generatedData)
            {{-- Step 2: preview + confirm --}}
            <x-vocabulary-card
                :word="$generatedData['word']"
                :pronunciation="$generatedData['pronunciation'] ?? null"
                :part-of-speech="$generatedData['part_of_speech'] ?? null"
                :simple-definition="$generatedData['simple_definition']"
                :detailed-definition="$generatedData['detailed_definition'] ?? null"
                :urdu-meaning="$generatedData['urdu_meaning'] ?? null"
                :examples="$generatedData['examples'] ?? []"
                :synonyms="$generatedData['synonyms'] ?? []"
                :antonyms="$generatedData['antonyms'] ?? []"
                :collocations="$generatedData['common_collocations'] ?? []"
                :ielts-relevance="$generatedData['ielts_relevance'] ?? null"
                :difficulty-level="$generatedData['difficulty_level'] ?? null"
            />

            <div class="flex gap-3">
                <button wire:click="save" type="button"
                    wire:loading.attr="disabled" wire:target="save"
                    class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-[var(--color-primary)] px-4 py-3 text-sm font-semibold text-[var(--color-on-primary)] hover:bg-[var(--color-primary-light)] disabled:opacity-60">
                    <x-icon name="check" class="size-4" />
                    Save to vocabulary
                </button>
                <button wire:click="discard" type="button"
                    class="rounded-lg border border-[var(--color-border)] px-4 py-3 text-sm font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-surface-muted)]">
                    Discard
                </button>
            </div>
        @endif
    </div>
