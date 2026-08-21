@props(['label', 'score', 'comment' => null])

<div>
    <div class="flex items-center justify-between text-sm">
        <span class="font-medium text-[var(--color-foreground)]">{{ $label }}</span>
        <span class="font-semibold tabular-nums text-[var(--color-primary)]">{{ $score }}</span>
    </div>
    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-[var(--color-surface-muted)]">
        <div class="h-full rounded-full bg-[var(--color-primary)]" style="width: {{ min(100, ($score / 9) * 100) }}%"></div>
    </div>
    @if ($comment)
        <p class="mt-1.5 text-sm text-[var(--color-foreground-muted)]">{{ $comment }}</p>
    @endif
</div>
