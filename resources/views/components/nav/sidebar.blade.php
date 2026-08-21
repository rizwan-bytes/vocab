@props(['class' => ''])

@php
    $items = [
        ['route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
        ['route' => 'vocabulary.index', 'active' => 'vocabulary.*', 'icon' => 'book-open', 'label' => 'Vocabulary'],
        ['route' => 'review.index', 'active' => 'review.*', 'icon' => 'arrow-path', 'label' => 'Review'],
        ['route' => 'progress.index', 'active' => 'progress.*', 'icon' => 'chart-bar', 'label' => 'Progress'],
        ['route' => 'tutor.index', 'active' => 'tutor.*', 'icon' => 'sparkles', 'label' => 'Tutor'],
    ];
@endphp

<nav {{ $attributes->class(['w-56 shrink-0', $class]) }} aria-label="Primary">
    <ul class="sticky top-24 space-y-1">
        @foreach ($items as $item)
            @php $active = request()->routeIs($item['active']); @endphp
            <li>
                <a href="{{ route($item['route']) }}" wire:navigate
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                        {{ $active
                            ? 'bg-[var(--color-surface-muted)] text-[var(--color-primary)]'
                            : 'text-[var(--color-foreground-muted)] hover:bg-[var(--color-surface-muted)] hover:text-[var(--color-foreground)]' }}"
                    aria-current="{{ $active ? 'page' : 'false' }}">
                    <x-icon :name="$item['icon']" class="size-5 {{ $active ? 'text-[var(--color-primary)]' : '' }}" />
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
