<div class="mx-auto max-w-2xl space-y-4">
    <div class="flex items-center justify-between">
        <a href="{{ route('vocabulary.index') }}" wire:navigate
            class="inline-flex items-center gap-1.5 text-sm font-medium text-[var(--color-foreground-muted)] hover:text-[var(--color-foreground)]">
            <x-icon name="chevron-right" class="size-3.5 rotate-180" />
            Vocabulary
        </a>

        <div class="flex items-center gap-2">
            @unless ($editing)
                <button wire:click="startEditing" type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--color-border)] px-3 py-1.5 text-sm font-medium text-[var(--color-foreground)] hover:bg-[var(--color-surface-muted)]">
                    <x-icon name="pencil" class="size-3.5" />
                    Edit
                </button>
            @endunless
            <button type="button"
                x-on:click="if (confirm('Delete \'{{ $word->word }}\'? This cannot be undone.')) $wire.delete()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--color-border)] px-3 py-1.5 text-sm font-medium text-[var(--color-danger)] hover:bg-red-50">
                <x-icon name="trash" class="size-3.5" />
                Delete
            </button>
        </div>
    </div>

    @if ($editing)
        <form wire:submit="save" class="space-y-4 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
            <h2 class="text-xl font-semibold text-[var(--color-foreground)]">{{ $word->word }}</h2>

            <div>
                <label for="simple_definition" class="block text-sm font-medium text-[var(--color-foreground)]">Simple meaning</label>
                <textarea wire:model="simple_definition" id="simple_definition" rows="2"
                    class="mt-1.5 block w-full rounded-lg border-[var(--color-border)] text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]"></textarea>
                @error('simple_definition') <p class="mt-1 text-sm text-[var(--color-danger)]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="detailed_definition" class="block text-sm font-medium text-[var(--color-foreground)]">Detailed definition</label>
                <textarea wire:model="detailed_definition" id="detailed_definition" rows="3"
                    class="mt-1.5 block w-full rounded-lg border-[var(--color-border)] text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]"></textarea>
            </div>

            <div>
                <label for="urdu_meaning" class="block text-sm font-medium text-[var(--color-foreground)]">Urdu meaning</label>
                <textarea wire:model="urdu_meaning" id="urdu_meaning" rows="2" dir="rtl"
                    class="font-urdu mt-1.5 block w-full rounded-lg border-[var(--color-border)] text-lg focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]"></textarea>
            </div>

            <div>
                <label for="context" class="block text-sm font-medium text-[var(--color-foreground)]">Context</label>
                <textarea wire:model="context" id="context" rows="2"
                    class="mt-1.5 block w-full rounded-lg border-[var(--color-border)] text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-[var(--color-primary)] px-4 py-2 text-sm font-semibold text-[var(--color-on-primary)] hover:bg-[var(--color-primary-light)]">
                    Save changes
                </button>
                <button type="button" wire:click="$set('editing', false)" class="rounded-lg border border-[var(--color-border)] px-4 py-2 text-sm font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-surface-muted)]">
                    Cancel
                </button>
            </div>
        </form>
    @else
        <x-vocabulary-card
            :word="$word->word"
            :pronunciation="$word->pronunciation"
            :part-of-speech="$word->part_of_speech"
            :simple-definition="$word->simple_definition"
            :detailed-definition="$word->detailed_definition"
            :urdu-meaning="$word->urdu_meaning"
            :examples="$word->examples ?? []"
            :synonyms="$word->synonyms ?? []"
            :antonyms="$word->antonyms ?? []"
            :collocations="$word->common_collocations ?? []"
            :ielts-relevance="$word->ielts_relevance"
            :difficulty-level="$word->difficulty_level"
            :status="$word->status"
            :next-review-at="$word->next_review_at"
        />

        @if ($word->context)
            <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">Found in context</h3>
                <p class="mt-1.5 text-sm italic text-[var(--color-foreground)]">"{{ $word->context }}"</p>
            </div>
        @endif
    @endif
</div>
