@props(['class' => ''])

@php
    $items = [
        ['route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'home', 'label' => 'Home'],
        ['route' => 'vocabulary.index', 'active' => 'vocabulary.*', 'icon' => 'book-open', 'label' => 'Words'],
        ['route' => 'review.index', 'active' => 'review.*', 'icon' => 'arrow-path', 'label' => 'Review'],
        ['route' => 'tutor.index', 'active' => 'tutor.*', 'icon' => 'sparkles', 'label' => 'Tutor'],
        ['route' => 'progress.index', 'active' => 'progress.*', 'icon' => 'chart-bar', 'label' => 'Progress'],
    ];
@endphp

<nav {{ $attributes->class(['fixed inset-x-0 bottom-0 z-40 border-t border-[var(--color-border)] bg-[var(--color-surface)] pb-[env(safe-area-inset-bottom)]', $class]) }}
    aria-label="Primary">
    <ul class="grid grid-cols-5">
        @foreach ($items as $item)
            @php $active = request()->routeIs($item['active']); @endphp
            <li>
                <a href="{{ route($item['route']) }}" wire:navigate
                    class="flex min-h-[56px] flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition
                        {{ $active ? 'text-[var(--color-primary)]' : 'text-[var(--color-foreground-muted)]' }}"
                    aria-current="{{ $active ? 'page' : 'false' }}">
                    <x-icon :name="$item['icon']" class="size-5" />
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
