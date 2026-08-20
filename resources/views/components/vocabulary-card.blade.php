@props([
    'word',
    'pronunciation' => null,
    'partOfSpeech' => null,
    'simpleDefinition',
    'detailedDefinition' => null,
    'urduMeaning' => null,
    'examples' => [],
    'synonyms' => [],
    'antonyms' => [],
    'collocations' => [],
    'ieltsRelevance' => null,
    'difficultyLevel' => null,
    'status' => null,
    'nextReviewAt' => null,
])

<div {{ $attributes->class(['rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 sm:p-8']) }}>
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-[var(--color-foreground)]">{{ $word }}</h2>
            <div class="mt-1.5 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-sm text-[var(--color-foreground-muted)]">
                @if ($pronunciation)
                    <span class="font-mono">{{ $pronunciation }}</span>
                @endif
                @if ($partOfSpeech)
                    <span class="rounded-full bg-[var(--color-surface-muted)] px-2 py-0.5 text-xs font-medium capitalize text-[var(--color-primary)]">{{ $partOfSpeech }}</span>
                @endif
                @if ($difficultyLevel)
                    <span class="rounded-full border border-[var(--color-border)] px-2 py-0.5 text-xs font-medium">{{ $difficultyLevel }}</span>
                @endif
            </div>
        </div>

        @if ($status)
            @php $color = $status->color(); @endphp
            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold"
                style="background-color: color-mix(in srgb, var(--color-status-{{ $status->value }}) 15%, white); color: var(--color-status-{{ $status->value }})">
                {{ $status->label() }}
            </span>
        @endif
    </div>

    {{-- Simple meaning --}}
    <p class="mt-5 text-lg leading-relaxed text-[var(--color-foreground)]">{{ $simpleDefinition }}</p>

    {{-- Urdu meaning --}}
    @if ($urduMeaning)
        <p lang="ur" class="font-urdu mt-2 text-xl text-[var(--color-foreground-muted)]">{{ $urduMeaning }}</p>
    @endif

    {{-- Detailed definition --}}
    @if ($detailedDefinition)
        <p class="mt-4 text-sm leading-relaxed text-[var(--color-foreground-muted)]">{{ $detailedDefinition }}</p>
    @endif

    <div class="mt-6 space-y-5 border-t border-[var(--color-border)] pt-5">
        {{-- Examples --}}
        @if (filled($examples))
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">Examples</h3>
                <ul class="mt-2 space-y-1.5">
                    @foreach ($examples as $example)
                        <li class="text-sm leading-relaxed text-[var(--color-foreground)] before:mr-2 before:text-[var(--color-primary)] before:content-['—']">{{ $example }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Synonyms / Antonyms --}}
        @if (filled($synonyms) || filled($antonyms))
            <div class="grid gap-4 sm:grid-cols-2">
                @if (filled($synonyms))
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">Synonyms</h3>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($synonyms as $synonym)
                                <span class="rounded-full bg-[var(--color-surface-muted)] px-2.5 py-1 text-xs font-medium text-[var(--color-primary)]">{{ $synonym }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (filled($antonyms))
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">Antonyms</h3>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($antonyms as $antonym)
                                <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-[var(--color-danger)]">{{ $antonym }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Collocations --}}
        @if (filled($collocations))
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">Common Collocations</h3>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach ($collocations as $collocation)
                        <span class="rounded-lg border border-[var(--color-border)] px-2.5 py-1 text-xs font-medium text-[var(--color-foreground)]">{{ $collocation }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- IELTS relevance --}}
        @if ($ieltsRelevance)
            <div class="flex gap-2.5 rounded-xl bg-[var(--color-surface-muted)] p-3.5">
                <x-icon name="academic-cap" class="mt-0.5 size-4 shrink-0 text-[var(--color-primary)]" />
                <p class="text-sm leading-relaxed text-[var(--color-foreground)]">{{ $ieltsRelevance }}</p>
            </div>
        @endif

        {{-- Review status --}}
        @if ($nextReviewAt)
            <div class="flex items-center gap-1.5 text-xs text-[var(--color-foreground-muted)]">
                <x-icon name="clock" class="size-3.5" />
                Next review: {{ $nextReviewAt->isPast() ? 'Due now' : $nextReviewAt->diffForHumans() }}
            </div>
        @endif
    </div>
</div>
