@php
    $feedback = $tutorSession->feedback;
    $isEssay = $tutorSession->type === \App\Enums\TutorSessionType::Essay;
    $isSpeaking = $tutorSession->type === \App\Enums\TutorSessionType::Speaking;
    $isVocab = $tutorSession->type === \App\Enums\TutorSessionType::VocabularyPractice;
@endphp

<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('tutor.index') }}" wire:navigate
            class="inline-flex items-center gap-1.5 text-sm font-medium text-[var(--color-foreground-muted)] hover:text-[var(--color-foreground)]">
            <x-icon name="chevron-right" class="size-3.5 rotate-180" />
            Tutor
        </a>
        <button type="button"
            x-on:click="if (confirm('Delete this session? This cannot be undone.')) $wire.delete()"
            class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--color-border)] px-3 py-1.5 text-sm font-medium text-[var(--color-danger)] hover:bg-red-50">
            <x-icon name="trash" class="size-3.5" />
            Delete
        </button>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[var(--color-foreground)]">{{ $tutorSession->type->label() }}</h1>
            <p class="mt-1 text-sm text-[var(--color-foreground-muted)]">{{ $tutorSession->created_at->format('M j, Y \a\t g:i A') }}</p>
        </div>

        @if ($tutorSession->band_overall !== null)
            <div class="text-center">
                <p class="text-3xl font-bold text-[var(--color-primary)]">{{ $tutorSession->band_overall }}</p>
                <p class="text-xs font-medium text-[var(--color-foreground-muted)]">Band Score</p>
            </div>
        @endif
    </div>

    {{-- Prompt / question / instructions --}}
    @if ($tutorSession->prompt_text)
        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface-muted)] p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">
                {{ $isSpeaking ? 'Question' : ($isVocab ? 'Exercise' : 'Topic') }}
            </p>
            <p class="mt-1.5 whitespace-pre-line text-sm text-[var(--color-foreground)]">{{ $tutorSession->prompt_text }}</p>
        </div>
    @endif

    {{-- Submission --}}
    <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-[var(--color-foreground-muted)]">Your submission</p>
        <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-[var(--color-foreground)]">{{ $tutorSession->submission }}</p>
    </div>

    {{-- Band breakdown (essay + speaking) --}}
    @if ($isEssay || $isSpeaking)
        <div class="space-y-4 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
            <h2 class="text-sm font-semibold text-[var(--color-foreground)]">Band Breakdown</h2>

            @if ($isEssay)
                <x-band-score label="Task Achievement" :score="$feedback['task_achievement']['score']" :comment="$feedback['task_achievement']['comment']" />
                <x-band-score label="Coherence &amp; Cohesion" :score="$feedback['coherence_cohesion']['score']" :comment="$feedback['coherence_cohesion']['comment']" />
                <x-band-score label="Lexical Resource" :score="$feedback['lexical_resource']['score']" :comment="$feedback['lexical_resource']['comment']" />
                <x-band-score label="Grammatical Range &amp; Accuracy" :score="$feedback['grammar']['score']" :comment="$feedback['grammar']['comment']" />
            @else
                <x-band-score label="Fluency &amp; Coherence" :score="$feedback['fluency_coherence']['score']" :comment="$feedback['fluency_coherence']['comment']" />
                <x-band-score label="Lexical Resource" :score="$feedback['lexical_resource']['score']" :comment="$feedback['lexical_resource']['comment']" />
                <x-band-score label="Grammatical Range &amp; Accuracy" :score="$feedback['grammar']['score']" :comment="$feedback['grammar']['comment']" />
                <p class="text-xs text-[var(--color-foreground-muted)]">Pronunciation isn't scored — this was typed, not spoken, practice.</p>
            @endif
        </div>

        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
            <p class="text-sm leading-relaxed text-[var(--color-foreground)]">{{ $feedback['summary'] }}</p>

            @if (filled($feedback['strengths']))
                <div class="mt-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[var(--color-status-mastered)]">Strengths</h3>
                    <ul class="mt-1.5 space-y-1">
                        @foreach ($feedback['strengths'] as $point)
                            <li class="flex gap-2 text-sm text-[var(--color-foreground)]">
                                <x-icon name="check" class="mt-0.5 size-4 shrink-0 text-[var(--color-status-mastered)]" />
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (filled($feedback['improvements']))
                <div class="mt-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-[var(--color-warning)]">To improve</h3>
                    <ul class="mt-1.5 space-y-1">
                        @foreach ($feedback['improvements'] as $point)
                            <li class="flex gap-2 text-sm text-[var(--color-foreground)]">
                                <x-icon name="exclamation-triangle" class="mt-0.5 size-4 shrink-0 text-[var(--color-warning)]" />
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Corrections (essay) --}}
        @if ($isEssay && filled($feedback['corrections'] ?? []))
            <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
                <h2 class="text-sm font-semibold text-[var(--color-foreground)]">Corrections</h2>
                <div class="mt-3 space-y-3">
                    @foreach ($feedback['corrections'] as $correction)
                        <div class="rounded-lg border border-[var(--color-border)] p-3">
                            <p class="text-sm text-[var(--color-danger)] line-through decoration-1">{{ $correction['original'] }}</p>
                            <p class="mt-1 text-sm font-medium text-[var(--color-status-mastered)]">{{ $correction['corrected'] }}</p>
                            <p class="mt-1.5 text-xs text-[var(--color-foreground-muted)]">{{ $correction['explanation'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Vocabulary suggestions (speaking) --}}
        @if ($isSpeaking && filled($feedback['vocabulary_suggestions'] ?? []))
            <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
                <h2 class="text-sm font-semibold text-[var(--color-foreground)]">Vocabulary Upgrades</h2>
                <div class="mt-3 space-y-3">
                    @foreach ($feedback['vocabulary_suggestions'] as $suggestion)
                        <div class="rounded-lg border border-[var(--color-border)] p-3">
                            <p class="text-sm">
                                <span class="text-[var(--color-foreground-muted)] line-through">{{ $suggestion['instead_of'] }}</span>
                                <span class="mx-1.5 text-[var(--color-foreground-muted)]">→</span>
                                <span class="font-medium text-[var(--color-primary)]">{{ $suggestion['use'] }}</span>
                            </p>
                            <p class="mt-1 text-xs text-[var(--color-foreground-muted)]">{{ $suggestion['reason'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- Vocabulary practice feedback --}}
    @if ($isVocab)
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
            <p class="text-sm leading-relaxed text-[var(--color-foreground)]">{{ $feedback['summary'] }}</p>

            <div class="mt-4 space-y-2">
                @foreach ($feedback['word_usage'] as $usage)
                    <div class="flex items-start gap-2.5 rounded-lg border border-[var(--color-border)] p-3">
                        <x-icon :name="$usage['used_correctly'] ? 'check' : 'x-mark'"
                            class="mt-0.5 size-4 shrink-0 {{ $usage['used_correctly'] ? 'text-[var(--color-status-mastered)]' : 'text-[var(--color-danger)]' }}" />
                        <div>
                            <p class="text-sm font-medium text-[var(--color-foreground)]">{{ $usage['word'] }}</p>
                            <p class="mt-0.5 text-sm text-[var(--color-foreground-muted)]">{{ $usage['feedback'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-4 border-t border-[var(--color-border)] pt-4 text-sm leading-relaxed text-[var(--color-foreground)]">{{ $feedback['overall_feedback'] }}</p>
        </div>
    @endif
</div>
