<div class="mx-auto max-w-xl space-y-6">
    <h1 class="text-2xl font-semibold tracking-tight text-[var(--color-foreground)]">Settings</h1>

    <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
        <h2 class="text-sm font-semibold text-[var(--color-foreground)]">Gemini API</h2>
        <p class="mt-1 text-sm text-[var(--color-foreground-muted)]">Used to generate vocabulary cards when you add a new word.</p>

        <div class="mt-4 flex items-center gap-2.5 rounded-lg {{ $geminiConfigured ? 'bg-emerald-50' : 'bg-red-50' }} p-3.5">
            <x-icon :name="$geminiConfigured ? 'check' : 'exclamation-triangle'" class="size-4 shrink-0 {{ $geminiConfigured ? 'text-[var(--color-success)]' : 'text-[var(--color-danger)]' }}" />
            <div class="text-sm">
                @if ($geminiConfigured)
                    <p class="font-medium text-[var(--color-success)]">Connected</p>
                    <p class="mt-0.5 font-mono text-xs text-[var(--color-foreground-muted)]">{{ $geminiKeyPreview }} · {{ $geminiModel }}</p>
                @else
                    <p class="font-medium text-[var(--color-danger)]">Not configured</p>
                    <p class="mt-0.5 text-xs text-[var(--color-foreground-muted)]">Set GEMINI_API_KEY in your .env file.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">
        <h2 class="text-sm font-semibold text-[var(--color-foreground)]">About</h2>
        <p class="mt-1.5 text-sm text-[var(--color-foreground-muted)]">
            Vocab is a personal vocabulary and IELTS preparation tool. Built for single-user use today —
            the data model is ready to support multiple accounts in the future.
        </p>
    </div>
</div>
