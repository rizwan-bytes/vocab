@props(['class' => ''])

@php
    $items = [
        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
        ['route' => 'vocabulary.index', 'icon' => 'book-open', 'label' => 'Vocabulary'],
        ['route' => 'review.index', 'icon' => 'arrow-path', 'label' => 'Review'],
        ['route' => 'progress.index', 'icon' => 'chart-bar', 'label' => 'Progress'],
    ];
@endphp

<nav {{ $attributes->class(['w-56 shrink-0', $class]) }} aria-label="Primary">
    <ul class="sticky top-24 space-y-1">
        @foreach ($items as $item)
            @php $active = request()->routeIs($item['route'].'*'); @endphp
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
