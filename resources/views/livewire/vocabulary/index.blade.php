<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold tracking-tight text-[var(--color-foreground)]">Vocabulary</h1>
        <a href="{{ route('vocabulary.create') }}" wire:navigate
            class="inline-flex items-center gap-1.5 rounded-lg bg-[var(--color-primary)] px-3.5 py-2 text-sm font-medium text-[var(--color-on-primary)] hover:bg-[var(--color-primary-light)]">
            <x-icon name="plus" class="size-4" />
            Add Word
        </a>
    </div>

    {{-- Search + filters --}}
    <div class="space-y-3 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
        <div class="relative">
            <x-icon name="magnifying-glass" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-[var(--color-foreground-muted)]" />
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search words or definitions…"
                class="w-full rounded-lg border-[var(--color-border)] py-2 pl-9 text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="status" class="rounded-lg border-[var(--color-border)] py-1.5 text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]">
                <option value="">All statuses</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>

            <select wire:model.live="difficulty" class="rounded-lg border-[var(--color-border)] py-1.5 text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]">
                <option value="">All levels</option>
                @foreach ($difficulties as $d)
                    <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>

            <label class="flex items-center gap-1.5 rounded-lg border border-[var(--color-border)] px-3 py-1.5 text-sm text-[var(--color-foreground-muted)]">
                <input wire:model.live="ielts" type="checkbox" value="1" class="rounded border-[var(--color-border)] text-[var(--color-primary)] focus:ring-[var(--color-primary)]">
                IELTS relevant
            </label>

            <select wire:model.live="sort" class="ml-auto rounded-lg border-[var(--color-border)] py-1.5 text-sm focus:border-[var(--color-primary)] focus:ring-[var(--color-primary)]">
                <option value="newest">Newest first</option>
                <option value="oldest">Oldest first</option>
            </select>

            @if ($search || $status || $difficulty || $ielts)
                <button wire:click="clearFilters" type="button" class="text-sm font-medium text-[var(--color-primary)] hover:underline">
                    Clear
                </button>
            @endif
        </div>
    </div>

    {{-- Results --}}
    @if ($words->isEmpty())
        <div class="rounded-xl border border-dashed border-[var(--color-border)] p-10 text-center">
            <x-icon name="book-open" class="mx-auto size-8 text-[var(--color-foreground-muted)]" />
            <p class="mt-3 text-sm font-medium text-[var(--color-foreground)]">
                @if ($search || $status || $difficulty || $ielts)
                    No words match your filters.
                @else
                    Your vocabulary is empty — add your first word to get started.
                @endif
            </p>
        </div>
    @else
        <div class="divide-y divide-[var(--color-border)] overflow-hidden rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)]">
            @foreach ($words as $word)
                <div class="flex items-center justify-between gap-4 p-4 transition hover:bg-[var(--color-surface-muted)]">
                    <a href="{{ route('vocabulary.show', $word) }}" wire:navigate class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-[var(--color-foreground)]">{{ $word->word }}</h3>
                            @if ($word->part_of_speech)
                                <span class="text-xs text-[var(--color-foreground-muted)]">{{ $word->part_of_speech }}</span>
                            @endif
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase"
                                style="background-color: color-mix(in srgb, var(--color-status-{{ $word->status->value }}) 15%, white); color: var(--color-status-{{ $word->status->value }})">
                                {{ $word->status->label() }}
                            </span>
                        </div>
                        <p class="mt-0.5 truncate text-sm text-[var(--color-foreground-muted)]">{{ $word->simple_definition }}</p>
                    </a>

                    <div class="flex shrink-0 items-center gap-1">
                        <a href="{{ route('vocabulary.show', $word) }}" wire:navigate
                            class="inline-flex size-9 items-center justify-center rounded-lg text-[var(--color-foreground-muted)] hover:bg-[var(--color-surface)] hover:text-[var(--color-primary)]"
                            aria-label="View {{ $word->word }}">
                            <x-icon name="chevron-right" class="size-4" />
                        </a>
                        <button type="button"
                            x-on:click="if (confirm('Delete \'{{ $word->word }}\'? This cannot be undone.')) $wire.delete({{ $word->id }})"
                            class="inline-flex size-9 items-center justify-center rounded-lg text-[var(--color-foreground-muted)] hover:bg-red-50 hover:text-[var(--color-danger)]"
                            aria-label="Delete {{ $word->word }}">
                            <x-icon name="trash" class="size-4" />
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div>{{ $words->links() }}</div>
    @endif
</div>
