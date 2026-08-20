@props(['class' => ''])

<header class="sticky top-0 z-40 border-b border-[var(--color-border)] bg-[var(--color-surface)]/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-3 px-4 sm:px-6">
        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 font-semibold text-[var(--color-foreground)]">
            <span class="flex size-8 items-center justify-center rounded-lg bg-[var(--color-primary)] text-[var(--color-on-primary)]">
                <x-icon name="academic-cap" class="size-5" />
            </span>
            <span class="text-lg tracking-tight">Vocab</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('vocabulary.create') }}" wire:navigate
                class="inline-flex items-center gap-1.5 rounded-lg bg-[var(--color-primary)] px-3.5 py-2 text-sm font-medium text-[var(--color-on-primary)] shadow-sm transition hover:bg-[var(--color-primary-light)] active:scale-95">
                <x-icon name="plus" class="size-4" />
                <span class="hidden sm:inline">Add Word</span>
            </a>

            <a href="{{ route('settings') }}" wire:navigate
                class="inline-flex size-9 items-center justify-center rounded-lg text-[var(--color-foreground-muted)] transition hover:bg-[var(--color-surface-muted)] hover:text-[var(--color-foreground)]"
                aria-label="Settings">
                <x-icon name="cog" class="size-5" />
            </a>
        </div>
    </div>
</header>
